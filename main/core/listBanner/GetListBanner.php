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

$sql_data = "SELECT
                banner_id, banner_order, banner_url, banner_image,
                banner_status, create_at, update_at
            FROM tbl_banner
            WHERE delete_at IS NULL
            ORDER BY banner_order ASC, banner_id ASC";

$stmt_data = $pdo_connect->prepare($sql_data);
$stmt_data->execute();
$result_data = $stmt_data->fetchAll(PDO::FETCH_ASSOC);
$stmt_data->closeCursor();

Response::json(1, 'Success', [
    'list_data' => $result_data,
]);
