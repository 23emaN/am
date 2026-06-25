<?php

use App\Utility\Auth;
use App\Utility\Response;
use App\Database\Connection;

$access_token = Auth::requireUserToken();
$admin_id = $access_token->user_id ?? null;

if (!$admin_id) {
    Response::json(0, 'Unauthorized', null);
}

$db_instance = new Connection();
$pdo_connect = $db_instance->getPdo();

if (!$pdo_connect) {
    Response::json(0, 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', null);
}

// ผู้ดูแลระบบ = admin_status = '1'
$sql_data = "SELECT user_id, user_firstname, user_lastname, user_email
             FROM tbl_user
             WHERE delete_at IS NULL AND admin_status = '1'
             ORDER BY user_id ASC";

$stmt_data = $pdo_connect->prepare($sql_data);
$stmt_data->execute();
$result_data = $stmt_data->fetchAll(PDO::FETCH_ASSOC);
$stmt_data->closeCursor();

$list_data = [];
foreach ($result_data as $row) {
    $list_data[] = [
        "user_id"    => $row["user_id"] ?? null,
        "full_name"  => trim(($row['user_firstname'] ?? '') . ' ' . ($row['user_lastname'] ?? '')),
        "user_email" => $row["user_email"] ?? null,
    ];
}

Response::json(1, 'Success', [
    'list_data'        => $list_data,
    'current_admin_id' => (int) $admin_id, // ใช้ซ่อนปุ่มลบของตัวเอง
]);
