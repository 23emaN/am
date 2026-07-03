<?php
// ตั้งค่าเว็บไซต์ (singleton) — บันทึกค่าลง tbl_website_setting + tbl_payment_methods
// มีแถวเดียว: ถ้ามีแถวอยู่แล้ว = UPDATE, ยังไม่มี = INSERT (ครอบด้วย transaction)

use App\Utility\Auth;
use App\Utility\Response;
use App\Database\Connection;

$access_token = Auth::requireUserToken();
$user_id = $access_token->user_id ?? null;

if (!$user_id) {
    Response::json(0, 'Unauthorized', null);
}

$db_instance = new Connection();
$pdo_connect = $db_instance->getPdo();

if (!$pdo_connect) {
    Response::json(0, 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', null);
}

/* ---------- helpers ---------- */
// อ่านค่า string (trim)
$s = function (string $key): string {
    return isset($_POST[$key]) ? trim((string) $_POST[$key]) : '';
};
// อ่านค่าเป็น flag '0'/'1'
$flag = function (string $key): string {
    return (isset($_POST[$key]) && (string) $_POST[$key] === '1') ? '1' : '0';
};

/* ---------- validate ---------- */
$department_code = $s('department_code');
if ($department_code === '') {
    Response::json(0, 'กรุณากรอกรหัสหน่วยงาน', null);
}

/* ---------- แถวเดิม (ถ้ามี) + รูปเดิม ---------- */
$existing = $pdo_connect->query("SELECT id, image_path FROM tbl_website_setting ORDER BY id ASC LIMIT 1")
    ->fetch(PDO::FETCH_ASSOC);
$image_path = $existing['image_path'] ?? '';

/* ---------- อัปโหลดรูปหน้าแรกใหม่ (ถ้ามี) ---------- */
if (!empty($_FILES['image_file']['name']) && (($_FILES['image_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK)) {

    $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
    $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));

    if (!isset($allowed[$ext])) {
        Response::json(0, 'รองรับเฉพาะไฟล์รูปภาพ (jpg, png, webp, gif)', null);
    }
    if ($_FILES['image_file']['size'] > 5 * 1024 * 1024) {
        Response::json(0, 'ขนาดรูปต้องไม่เกิน 5 MB', null);
    }

    $rootDir   = dirname(__DIR__, 3);
    $uploadDir = $rootDir . '/upload/website/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        Response::json(0, 'ไม่สามารถสร้างโฟลเดอร์อัปโหลดได้', null);
    }

    $filename = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $filename)) {
        Response::json(0, 'อัปโหลดรูปไม่สำเร็จ', null);
    }

    // ลบรูปเก่า (ถ้ามี)
    if ($image_path && file_exists($rootDir . '/' . $image_path)) {
        @unlink($rootDir . '/' . $image_path);
    }

    $image_path = 'upload/website/' . $filename;
}

/* ---------- ฟิลด์ของ tbl_website_setting ---------- */
$fields = [
    'department_code'  => $department_code,
    'allow_skip_video' => $flag('allow_skip_video'),
    'otp_enabled'      => $flag('otp_enabled'),
    'tax_enabled'      => $flag('tax_enabled'),
    'tax_id'           => $s('tax_id'),
    'youtube_id'       => $s('youtube_id'),
    'text_1'           => isset($_POST['text_1']) ? trim((string) $_POST['text_1']) : '',
    'image_path'       => $image_path,
    'text_2'           => isset($_POST['text_2']) ? trim((string) $_POST['text_2']) : '',
    'facebook_link'    => $s('facebook_link'),
    'x_link'           => $s('x_link'),
    'line_link'        => $s('line_link'),
    'about_us'         => isset($_POST['about_us']) ? trim((string) $_POST['about_us']) : '',
    'contact_us'       => isset($_POST['contact_us']) ? trim((string) $_POST['contact_us']) : '',
    'branch_code'      => $s('branch_code'),
];

/* ---------- ฟิลด์ของ tbl_payment_methods ---------- */
$pay = [
    'credit_card'   => $flag('credit_card'),
    'qr_promptpay'  => $flag('qr_promptpay'),
    'bank_transfer' => $flag('bank_transfer'),
];

/* ---------- บันทึก (upsert ทั้งสองตารางใน transaction เดียว) ---------- */
try {
    $pdo_connect->beginTransaction();

    // tbl_website_setting
    if ($existing) {
        $set = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($fields)));
        $st  = $pdo_connect->prepare("UPDATE tbl_website_setting SET $set WHERE id = :id");
        $st->execute($fields + ['id' => $existing['id']]);
        $st->closeCursor();
    } else {
        $cols = implode(', ', array_keys($fields));
        $ph   = implode(', ', array_map(fn($c) => ":$c", array_keys($fields)));
        $st   = $pdo_connect->prepare("INSERT INTO tbl_website_setting ($cols) VALUES ($ph)");
        $st->execute($fields);
        $st->closeCursor();
    }

    // tbl_payment_methods
    $pexist = $pdo_connect->query("SELECT payment_id FROM tbl_payment_methods ORDER BY payment_id ASC LIMIT 1")
        ->fetch(PDO::FETCH_ASSOC);
    if ($pexist) {
        $st = $pdo_connect->prepare(
            "UPDATE tbl_payment_methods SET credit_card = :credit_card, qr_promptpay = :qr_promptpay, bank_transfer = :bank_transfer WHERE payment_id = :pid"
        );
        $st->execute($pay + ['pid' => $pexist['payment_id']]);
        $st->closeCursor();
    } else {
        $st = $pdo_connect->prepare(
            "INSERT INTO tbl_payment_methods (credit_card, qr_promptpay, bank_transfer) VALUES (:credit_card, :qr_promptpay, :bank_transfer)"
        );
        $st->execute($pay);
        $st->closeCursor();
    }

    $pdo_connect->commit();
    Response::json(1, 'บันทึกการตั้งค่าสำเร็จ', null);
} catch (Exception $e) {
    if ($pdo_connect->inTransaction()) {
        $pdo_connect->rollBack();
    }
    error_log('Update Website Setting Error: ' . $e->getMessage());
    Response::json(0, 'เกิดข้อผิดพลาดในการบันทึกข้อมูล', null);
} finally {
    $pdo_connect = null;
}
