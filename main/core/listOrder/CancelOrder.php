<?php
// ยกเลิกคำสั่งซื้อ (เฉพาะออเดอร์ที่ยังรอชำระเงิน): payment_status '0' -> '2'
// ไม่แตะ enrollment เพราะออเดอร์ที่ยกเลิกได้ยังไม่ชำระ จึงยังไม่เคยให้สิทธิ์เข้าเรียน

use App\Utility\Auth;
use App\Utility\Response;
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
    // ตรวจสถานะปัจจุบันก่อนยกเลิก
    $stmt = $pdo_connect->prepare(
        "SELECT payment_status FROM tbl_orders WHERE order_id = :id LIMIT 1"
    );
    $stmt->execute([':id' => $order_id]);
    $status = $stmt->fetchColumn();
    $stmt->closeCursor();

    if ($status === false) {
        Response::json(0, 'ไม่พบคำสั่งซื้อนี้', null);
    }
    if ((string) $status === '2') {
        Response::json(0, 'คำสั่งซื้อนี้ถูกยกเลิกไปแล้ว', null);
    }
    if ((string) $status === '1') {
        Response::json(0, 'ไม่สามารถยกเลิกคำสั่งซื้อที่ชำระเงินแล้วได้', null);
    }

    // ยกเลิก: '0' -> '2'
    $upd = $pdo_connect->prepare(
        "UPDATE tbl_orders SET payment_status = '2'
         WHERE order_id = :id AND payment_status = '0'"
    );
    $upd->execute([':id' => $order_id]);
    $affected = $upd->rowCount();
    $upd->closeCursor();

    if ($affected === 0) {
        Response::json(0, 'ไม่สามารถยกเลิกคำสั่งซื้อได้ (อาจถูกดำเนินการไปแล้ว)', null);
    }

    Response::json(1, 'ยกเลิกคำสั่งซื้อเรียบร้อยแล้ว', ['order_id' => $order_id]);
} catch (\Throwable $e) {
    error_log('CancelOrder Error: ' . $e->getMessage());
    Response::json(0, 'เกิดข้อผิดพลาด', null);
} finally {
    $pdo_connect = null;
}
