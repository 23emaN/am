<?php

use App\Utility\Auth;
use App\Utility\Response;
use App\Database\Connection;

$access_token = Auth::requireUserToken();
$admin_id = $access_token->user_id ?? null;

if (!$admin_id) {
    Response::json(0, 'Unauthorized', null);
}

$target_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
if ($target_id <= 0) {
    Response::json(0, 'ไม่พบรหัสผู้ดูแลระบบ', null);
}

$db_instance = new Connection();
$pdo_connect = $db_instance->getPdo();

if (!$pdo_connect) {
    Response::json(0, 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', null);
}

$stmt = $pdo_connect->prepare(
    "SELECT user_id, user_firstname, user_lastname, user_email
     FROM tbl_user
     WHERE user_id = :id AND admin_status = '1' AND delete_at IS NULL LIMIT 1"
);
$stmt->execute([':id' => $target_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

if (!$row) {
    Response::json(0, 'ไม่พบผู้ดูแลระบบนี้', null);
}

$admin = [
    'user_id'    => $row['user_id'],
    'full_name'  => trim(($row['user_firstname'] ?? '') . ' ' . ($row['user_lastname'] ?? '')),
    'user_email' => $row['user_email'],
];

Response::json(1, 'Success', ['admin' => $admin]);
