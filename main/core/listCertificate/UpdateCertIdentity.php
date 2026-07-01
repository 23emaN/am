<?php
// ปรับปรุงไฟล์ยืนยันตัวตนในเอกสาร -> ตั้งรูปในใบรับรอง (tbl_user.id_card_image)
// ให้เป็นรูปเอกสารปัจจุบันของผู้ใช้ (tbl_user.current_photo)
// ใช้เมื่อรูปในใบรับรองไม่ตรงกับรูปปัจจุบัน (ผู้ใช้อัปเดตเอกสารใหม่)

use App\Utility\Auth;
use App\Utility\Response;
use App\Database\Connection;

$access_token = Auth::requireUserToken();
$admin_id = $access_token->user_id ?? null;
if (!$admin_id) {
    Response::json(0, 'Unauthorized', null);
}

$enroll_id = isset($_POST['enroll_id']) ? (int) $_POST['enroll_id'] : 0;
if ($enroll_id <= 0) {
    Response::json(0, 'ไม่พบรายการ', null);
}

$db_instance = new Connection();
$pdo_connect = $db_instance->getPdo();
if (!$pdo_connect) {
    Response::json(0, 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', null);
}

// หา user ของ enrollment นี้ + รูปเอกสารปัจจุบัน
$stmt = $pdo_connect->prepare(
    "SELECT u.user_id, u.current_photo, u.id_card_image
       FROM tbl_course_enrollment e
       LEFT JOIN tbl_user u ON e.enroll_user_id = u.user_id
      WHERE e.enroll_id = :id AND e.delete_at IS NULL LIMIT 1"
);
$stmt->execute([':id' => $enroll_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

if (!$row || empty($row['user_id'])) {
    Response::json(0, 'ไม่พบผู้ใช้ของรายการนี้', null);
}
$current = trim((string) ($row['current_photo'] ?? ''));
if ($current === '') {
    Response::json(0, 'ไม่พบรูปเอกสารปัจจุบันของผู้ใช้', null);
}
if ($current === trim((string) ($row['id_card_image'] ?? ''))) {
    Response::json(0, 'รูปในใบรับรองตรงกับรูปปัจจุบันอยู่แล้ว', null);
}

$upd = $pdo_connect->prepare(
    "UPDATE tbl_user SET id_card_image = :img WHERE user_id = :uid AND delete_at IS NULL"
);
$upd->execute([':img' => $current, ':uid' => (int) $row['user_id']]);

Response::json(1, 'ปรับปรุงไฟล์ยืนยันตัวตนในเอกสารเรียบร้อยแล้ว', ['enroll_id' => $enroll_id]);
