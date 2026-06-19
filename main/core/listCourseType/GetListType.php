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

$sql_data = "SELECT t.type_id,
                    t.type_name,
                    COUNT(c.course_id) AS course_count
             FROM tbl_course_type t
             LEFT JOIN tbl_course c ON c.course_type = t.type_id AND c.delete_at IS NULL
             WHERE t.delete_at IS NULL
             GROUP BY t.type_id, t.type_name
             ORDER BY t.type_id ASC";

$stmt_data = $pdo_connect->prepare($sql_data);
$stmt_data->execute();
$result_data = $stmt_data->fetchAll(PDO::FETCH_ASSOC);
$stmt_data->closeCursor();

$list_data = [];
foreach ($result_data as $row) {
    $list_data[] = [
        "type_id"      => $row["type_id"] ?? null,
        "type_name"    => $row["type_name"] ?? null,
        "course_count" => $row["course_count"] ?? 0,
    ];
}

Response::json(1, 'Success', [
    'list_data' => $list_data,
]);
