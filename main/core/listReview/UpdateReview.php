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

$review_id   = isset($_POST['review_id']) ? (int) $_POST['review_id'] : 0;
$rating      = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
$comment     = isset($_POST['comment']) ? trim($_POST['comment']) : '';
$is_approved = isset($_POST['is_approved']) && (string) $_POST['is_approved'] === '1' ? '1' : '0';

if ($review_id <= 0) {
    Response::json(0, 'ไม่พบรหัสรีวิว', null);
}
if ($rating < 1 || $rating > 5) {
    Response::json(0, 'กรุณาระบุคะแนนระหว่าง 1-5', null);
}
if ($comment === '') {
    Response::json(0, 'กรุณากรอกข้อความรีวิว', null);
}

// ตรวจว่ารีวิวมีจริง + ดึงรูปเดิม
$check = $pdo_connect->prepare("SELECT reviewer_image FROM tbl_reviews WHERE review_id = :id LIMIT 1");
$check->execute([':id' => $review_id]);
$existing_row = $check->fetch(PDO::FETCH_ASSOC);
$check->closeCursor();
if (!$existing_row) {
    Response::json(0, 'ไม่พบรีวิวนี้ หรือถูกลบไปแล้ว', null);
}
$existing_image = (string) ($existing_row['reviewer_image'] ?? '');

/* ---------- รูปผู้รีวิว: อัปใหม่ = แทนที่ / กดลบ = ล้าง / ไม่ทำอะไร = คงรูปเดิม (ลอก pattern จาก website_setting) ---------- */
$reviewer_image = $existing_image;
$rootDir = dirname(__DIR__, 3);   // .../backoffice
if (!empty($_FILES['reviewer_image']['name']) && (($_FILES['reviewer_image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK)) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ext = strtolower(pathinfo($_FILES['reviewer_image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        Response::json(0, 'รองรับเฉพาะไฟล์รูปภาพ (jpg, png, webp, gif)', null);
    }
    if ($_FILES['reviewer_image']['size'] > 5 * 1024 * 1024) {
        Response::json(0, 'ขนาดรูปต้องไม่เกิน 5 MB', null);
    }
    $uploadDir = $rootDir . '/upload/review/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        Response::json(0, 'ไม่สามารถสร้างโฟลเดอร์อัปโหลดได้', null);
    }
    $filename = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($_FILES['reviewer_image']['tmp_name'], $uploadDir . $filename)) {
        Response::json(0, 'อัปโหลดรูปไม่สำเร็จ', null);
    }
    if ($existing_image && file_exists($rootDir . '/' . $existing_image)) {
        @unlink($rootDir . '/' . $existing_image);   // ลบรูปเก่า
    }
    $reviewer_image = 'upload/review/' . $filename;
} elseif (($_POST['remove_image'] ?? '0') === '1') {
    if ($existing_image && file_exists($rootDir . '/' . $existing_image)) {
        @unlink($rootDir . '/' . $existing_image);
    }
    $reviewer_image = '';
}

try {
    $stmt = $pdo_connect->prepare(
        "UPDATE tbl_reviews SET rating = :rating, comment = :comment, is_approved = :is_approved, reviewer_image = :reviewer_image WHERE review_id = :id"
    );
    $stmt->execute([
        ':rating'         => $rating,
        ':comment'        => $comment,
        ':is_approved'    => $is_approved,
        ':reviewer_image' => $reviewer_image !== '' ? $reviewer_image : null,
        ':id'             => $review_id,
    ]);
    $stmt->closeCursor();

    Response::json(1, 'บันทึกข้อมูลรีวิวสำเร็จ', ['review_id' => $review_id]);
} catch (Exception $e) {
    error_log('Update Review Error: ' . $e->getMessage());
    Response::json(0, 'เกิดข้อผิดพลาดในการบันทึกข้อมูล', null);
} finally {
    $pdo_connect = null;
}
