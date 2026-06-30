<?php
// ยืนยันการชำระเงิน (เฉพาะออเดอร์โอนเงินที่รอยืนยัน):
//   payment_status '0' -> '1' + สร้างสิทธิ์เข้าเรียน (enrollment) ต่อคอร์ส แล้วส่งอีเมลแจ้งลูกค้า (best-effort)
// ลอกตรรกะการให้สิทธิ์จาก cpdth/gb_webhook.php (เมื่อชำระเงินสำเร็จ) + อีเมลจาก listEnrollment/AddEnrollment.php

use App\Utility\Auth;
use App\Utility\Response;
use App\Utility\Email;
use App\Database\Connection;

$access_token = Auth::requireUserToken();
$admin_id = $access_token->user_id ?? null;
if (!$admin_id) {
    Response::json(0, 'Unauthorized', null);
}

$order_id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
if ($order_id <= 0) {
    Response::json(0, 'ไม่พบรหัสคำสั่งซื้อ', null);
}

$db_instance = new Connection();
$pdo_connect = $db_instance->getPdo();
if (!$pdo_connect) {
    Response::json(0, 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', null);
}

try {
    $pdo_connect->beginTransaction();

    // 1) ดึงออเดอร์ + ตรวจสถานะ/วิธีชำระ
    $stmt = $pdo_connect->prepare(
        "SELECT order_id, user_id, payment_status, payment_method
         FROM tbl_orders WHERE order_id = :id LIMIT 1"
    );
    $stmt->execute([':id' => $order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if (!$order) {
        $pdo_connect->rollBack();
        Response::json(0, 'ไม่พบคำสั่งซื้อนี้', null);
    }
    if ((string) $order['payment_status'] !== '0') {
        $pdo_connect->rollBack();
        Response::json(0, 'คำสั่งซื้อนี้ชำระเงินหรือยกเลิกไปแล้ว ไม่สามารถยืนยันซ้ำได้', null);
    }
    if ((string) $order['payment_method'] !== '2') {
        $pdo_connect->rollBack();
        Response::json(0, 'รองรับเฉพาะคำสั่งซื้อแบบโอนเงินผ่านธนาคารเท่านั้น', null);
    }

    $user_id = (int) $order['user_id'];

    // 2) อัปเดตสถานะ -> ชำระเงินสำเร็จ (กันชนกับการกดซ้ำด้วยเงื่อนไข payment_status='0')
    $upd = $pdo_connect->prepare(
        "UPDATE tbl_orders SET payment_status = '1'
         WHERE order_id = :id AND payment_status = '0'"
    );
    $upd->execute([':id' => $order_id]);
    $affected = $upd->rowCount();
    $upd->closeCursor();

    if ($affected === 0) {
        $pdo_connect->rollBack();
        Response::json(0, 'ไม่สามารถอัปเดตสถานะคำสั่งซื้อได้ (อาจถูกดำเนินการไปแล้ว)', null);
    }

    // 3) ดึงรายการคอร์สในออเดอร์ + ระยะเวลาเรียน
    $it = $pdo_connect->prepare(
        "SELECT od.course_id, c.course_period, c.course_name
         FROM tbl_order_detail od
         LEFT JOIN tbl_course c ON c.course_id = od.course_id
         WHERE od.order_id = :id
         ORDER BY od.list_order ASC, od.detail_id ASC"
    );
    $it->execute([':id' => $order_id]);
    $items = $it->fetchAll(PDO::FETCH_ASSOC);
    $it->closeCursor();

    if (empty($items)) {
        $pdo_connect->rollBack();
        Response::json(0, 'ไม่พบรายการคอร์สในคำสั่งซื้อนี้', null);
    }

    // 4) สร้างสิทธิ์เข้าเรียน (enrollment) ต่อคอร์ส — expiry = วันนี้ + course_period วัน (ถ้า > 0)
    $now = date('Y-m-d H:i:s');
    $enr = $pdo_connect->prepare(
        "INSERT INTO tbl_course_enrollment
            (enroll_user_id, enroll_course_id, enroll_payment_status,
             enroll_date, enroll_expiry_date, enroll_is_completed)
         VALUES (:uid, :cid, 'paid', :enroll_date, :expiry_date, '0')"
    );
    $course_names = [];
    foreach ($items as $item) {
        $course_id = (int) $item['course_id'];
        $period    = isset($item['course_period']) ? (int) $item['course_period'] : 0;
        $expiry    = $period > 0 ? date('Y-m-d H:i:s', strtotime("$now +$period days")) : null;

        $enr->execute([
            ':uid'         => $user_id,
            ':cid'         => $course_id,
            ':enroll_date' => $now,
            ':expiry_date' => $expiry,
        ]);
        $course_names[] = (string) ($item['course_name'] ?? '');
    }
    $enr->closeCursor();

    // 5) ดึงข้อมูลลูกค้าไว้ส่งอีเมล (ทำก่อน commit เพราะยังอยู่ใน transaction เดียวกัน)
    $u = $pdo_connect->prepare(
        "SELECT user_email, user_firstname, user_lastname
         FROM tbl_user WHERE user_id = :uid LIMIT 1"
    );
    $u->execute([':uid' => $user_id]);
    $user = $u->fetch(PDO::FETCH_ASSOC) ?: [];
    $u->closeCursor();

    $pdo_connect->commit();

    // 6) ส่งอีเมลแจ้งลูกค้า (best-effort — ไม่ทำให้รายการล้มเหลวถ้าส่งไม่ได้)
    $mail_sent = false;
    $email = trim((string) ($user['user_email'] ?? ''));
    if ($email !== '') {
        try {
            $name = trim(($user['user_firstname'] ?? '') . ' ' . ($user['user_lastname'] ?? ''));
            $list = array_filter(array_map('trim', $course_names), fn($x) => $x !== '');
            $course_li = $list
                ? '<ul style="margin:6px 0 0 0;padding-left:18px;">'
                    . implode('', array_map(fn($x) => '<li>' . htmlspecialchars($x) . '</li>', $list))
                    . '</ul>'
                : '';
            $body = '<div style="font-family:Tahoma,Arial,sans-serif;font-size:14px;color:#222;">'
                . '<p>เรียน คุณ' . htmlspecialchars($name !== '' ? $name : 'ลูกค้า') . '</p>'
                . '<p>ระบบได้รับยืนยันการชำระเงินของคำสั่งซื้อเรียบร้อยแล้ว คุณสามารถเข้าเรียนได้ทันที</p>'
                . '<p><b>รายการคอร์ส:</b>' . $course_li . '</p>'
                . '<p>เข้าเรียนได้ที่เว็บไซต์ CPDTH</p>'
                . '<p style="color:#888;font-size:12px;">อีเมลฉบับนี้ส่งจากระบบ CPDTH โดยอัตโนมัติ</p></div>';
            $mail_sent = (bool) Email::send($email, 'ยืนยันการชำระเงิน - CPDTH', $body, true);
        } catch (\Throwable $eMail) {
            error_log('ConfirmPayment mail error: ' . $eMail->getMessage());
            $mail_sent = false;
        }
    }

    Response::json(1, 'ยืนยันการชำระเงินสำเร็จ และสร้างสิทธิ์เข้าเรียนแล้ว', [
        'order_id'         => $order_id,
        'courses_enrolled' => count($items),
        'mail_sent'        => $mail_sent,
    ]);

} catch (\Throwable $e) {
    if ($pdo_connect->inTransaction()) {
        $pdo_connect->rollBack();
    }
    error_log('ConfirmPayment Error: ' . $e->getMessage());
    Response::json(0, 'เกิดข้อผิดพลาด: ' . $e->getMessage(), null);
} finally {
    $pdo_connect = null;
}
