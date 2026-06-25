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

// คำขอยืนยันตัวตน = ผู้ใช้ที่ส่งเอกสารแล้วและอยู่ระหว่างดำเนินการ (identity_verified = '1')
$sql_data = "SELECT
                u.user_id,
                u.user_firstname,
                u.user_lastname,
                u.user_email,
                u.user_phone,
                u.user_citizen_id,
                u.user_cpd_no,
                u.user_cpa_no
            FROM tbl_user u
            WHERE u.delete_at IS NULL
              AND u.identity_verified = '1'
            ORDER BY u.user_id DESC";

$stmt_data = $pdo_connect->prepare($sql_data);
$stmt_data->execute();
$result_data = $stmt_data->fetchAll(PDO::FETCH_ASSOC);
$stmt_data->closeCursor();

$list_data = [];
foreach ($result_data as $row) {
    $list_data[] = [
        "user_id"         => $row["user_id"] ?? null,
        "user_firstname"  => $row["user_firstname"] ?? null,
        "user_lastname"   => $row["user_lastname"] ?? null,
        "user_email"      => $row["user_email"] ?? null,
        "user_phone"      => $row["user_phone"] ?? null,
        "user_citizen_id" => $row["user_citizen_id"] ?? null,
        "user_cpd_no"     => $row["user_cpd_no"] ?? null,
        "user_cpa_no"     => $row["user_cpa_no"] ?? null,
    ];
}

Response::json(1, 'Success', [
    'list_data' => $list_data,
]);
