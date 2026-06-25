<?php

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
$str = function (string $key): ?string {
    $v = isset($_POST[$key]) ? trim($_POST[$key]) : '';
    return $v === '' ? null : $v;
};

/* ---------- validate ---------- */
$banner_id     = isset($_POST['banner_id']) ? (int) $_POST['banner_id'] : 0;
$banner_order  = isset($_POST['banner_order']) ? (int) $_POST['banner_order'] : 0;
$banner_url    = $str('banner_url');
$banner_status = isset($_POST['banner_status']) ? trim($_POST['banner_status']) : '1';
$banner_status = ($banner_status === '1') ? '1' : '0';

if ($banner_id <= 0) {
    Response::json(0, 'ไม่พบรหัสแบนเนอร์', null);
}
if ($banner_order <= 0) {
    Response::json(0, 'กรุณากรอกลำดับการแสดง (ต้องมากกว่า 0)', null);
}

// ตรวจว่าแบนเนอร์มีจริงและยังไม่ถูกลบ
$check = $pdo_connect->prepare("SELECT banner_id, banner_image FROM tbl_banner WHERE banner_id = :id AND delete_at IS NULL LIMIT 1");
$check->execute([':id' => $banner_id]);
$old = $check->fetch(PDO::FETCH_ASSOC);
$check->closeCursor();
if (!$old) {
    Response::json(0, 'ไม่พบแบนเนอร์นี้ หรือถูกลบไปแล้ว', null);
}

// ลำดับห้ามซ้ำกับแบนเนอร์อื่น
$dup = $pdo_connect->prepare("SELECT banner_id FROM tbl_banner WHERE banner_order = :order AND banner_id <> :id AND delete_at IS NULL LIMIT 1");
$dup->execute([':order' => $banner_order, ':id' => $banner_id]);
if ($dup->fetchColumn()) {
    Response::json(0, 'ลำดับการแสดงนี้ถูกใช้งานแล้ว กรุณาเลือกลำดับอื่น', null);
}
$dup->closeCursor();

/* ---------- อัปโหลดรูปแบนเนอร์ใหม่ (ถ้ามี) ---------- */
$banner_image = $old['banner_image']; // ใช้รูปเดิมถ้าไม่ได้อัปโหลดใหม่

if (!empty($_FILES['banner_image']['name']) && ($_FILES['banner_image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {

    $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
    $ext = strtolower(pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION));

    if (!isset($allowed[$ext])) {
        Response::json(0, 'รองรับเฉพาะไฟล์รูปภาพ (jpg, png, webp, gif)', null);
    }
    if ($_FILES['banner_image']['size'] > 5 * 1024 * 1024) {
        Response::json(0, 'ขนาดรูปต้องไม่เกิน 5 MB', null);
    }

    $rootDir   = dirname(__DIR__, 3);
    $uploadDir = $rootDir . '/upload/banner/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        Response::json(0, 'ไม่สามารถสร้างโฟลเดอร์อัปโหลดได้', null);
    }

    $filename = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($_FILES['banner_image']['tmp_name'], $uploadDir . $filename)) {
        Response::json(0, 'อัปโหลดรูปไม่สำเร็จ', null);
    }

    // ลบรูปเก่า (ถ้ามี)
    $old_path = $rootDir . '/' . $old['banner_image'];
    if ($old['banner_image'] && file_exists($old_path)) {
        @unlink($old_path);
    }

    $banner_image = 'upload/banner/' . $filename;
}

/* ---------- update ---------- */
try {
    $sql = "UPDATE tbl_banner SET
                banner_order  = :order,
                banner_url    = :url,
                banner_image  = :image,
                banner_status = :status
            WHERE banner_id = :id AND delete_at IS NULL";
    $stmt = $pdo_connect->prepare($sql);
    $stmt->execute([
        ':order'  => $banner_order,
        ':url'    => $banner_url,
        ':image'  => $banner_image,
        ':status' => $banner_status,
        ':id'     => $banner_id,
    ]);
    $stmt->closeCursor();

    Response::json(1, 'บันทึกข้อมูลแบนเนอร์สำเร็จ', ['banner_id' => $banner_id]);
} catch (Exception $e) {
    error_log('Update Banner Error: ' . $e->getMessage());
    Response::json(0, 'เกิดข้อผิดพลาดในการบันทึกข้อมูล', null);
} finally {
    $pdo_connect = null;
}
