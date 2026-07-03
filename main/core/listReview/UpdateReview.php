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

// ตรวจว่ารีวิวมีจริง
$check = $pdo_connect->prepare("SELECT review_id FROM tbl_reviews WHERE review_id = :id LIMIT 1");
$check->execute([':id' => $review_id]);
if (!$check->fetchColumn()) {
    Response::json(0, 'ไม่พบรีวิวนี้ หรือถูกลบไปแล้ว', null);
}
$check->closeCursor();

try {
    $stmt = $pdo_connect->prepare(
        "UPDATE tbl_reviews SET rating = :rating, comment = :comment, is_approved = :is_approved WHERE review_id = :id"
    );
    $stmt->execute([
        ':rating'      => $rating,
        ':comment'     => $comment,
        ':is_approved' => $is_approved,
        ':id'          => $review_id,
    ]);
    $stmt->closeCursor();

    Response::json(1, 'บันทึกข้อมูลรีวิวสำเร็จ', ['review_id' => $review_id]);
} catch (Exception $e) {
    error_log('Update Review Error: ' . $e->getMessage());
    Response::json(0, 'เกิดข้อผิดพลาดในการบันทึกข้อมูล', null);
} finally {
    $pdo_connect = null;
}
