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

/* ---------- helpers ---------- */
$str = function (string $key): ?string {
    $v = isset($_POST[$key]) ? trim($_POST[$key]) : '';
    return $v === '' ? null : $v;
};

/* ---------- validate ---------- */
$target_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
if ($target_id <= 0) {
    Response::json(0, 'ไม่พบรหัสผู้ใช้', null);
}

$prefix     = isset($_POST['user_prefix']) && $_POST['user_prefix'] !== '' ? (int) $_POST['user_prefix'] : null;
$firstname  = $str('user_firstname');
$lastname   = $str('user_lastname');
$email      = $str('user_email');
$phone      = $str('user_phone');
$citizen_id = $str('user_citizen_id');
$cpd_no     = $str('user_cpd_no');
$cpa_no     = $str('user_cpa_no');
$password         = $str('user_password');
$password_confirm = $str('user_password_confirm');

if ($firstname === null) {
    Response::json(0, 'กรุณากรอกชื่อ', null);
}
if ($lastname === null) {
    Response::json(0, 'กรุณากรอกนามสกุล', null);
}
if ($email === null || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Response::json(0, 'กรุณากรอกอีเมลให้ถูกต้อง', null);
}
if ($phone === null) {
    Response::json(0, 'กรุณากรอกเบอร์โทรศัพท์', null);
}
if ($citizen_id === null) {
    Response::json(0, 'กรุณากรอกเลขบัตรประชาชน', null);
}
if ($prefix !== null && !in_array($prefix, [1, 2, 3], true)) {
    Response::json(0, 'คำนำหน้าไม่ถูกต้อง', null);
}

// ถ้ากรอกรหัสผ่าน ต้องตรงกับยืนยันรหัสผ่าน
$change_password = false;
if ($password !== null || $password_confirm !== null) {
    if ($password !== $password_confirm) {
        Response::json(0, 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน', null);
    }
    $change_password = true;
}

// ตรวจว่าผู้ใช้มีจริงและยังไม่ถูกลบ
$check = $pdo_connect->prepare("SELECT user_id FROM tbl_user WHERE user_id = :id AND delete_at IS NULL LIMIT 1");
$check->execute([':id' => $target_id]);
$exists = $check->fetchColumn();
$check->closeCursor();
if (!$exists) {
    Response::json(0, 'ไม่พบผู้ใช้นี้ หรือถูกลบไปแล้ว', null);
}

// อีเมลห้ามซ้ำกับผู้ใช้คนอื่น
$dup = $pdo_connect->prepare("SELECT user_id FROM tbl_user WHERE user_email = :email AND user_id <> :id AND delete_at IS NULL LIMIT 1");
$dup->execute([':email' => $email, ':id' => $target_id]);
$dup_id = $dup->fetchColumn();
$dup->closeCursor();
if ($dup_id) {
    Response::json(0, 'อีเมลนี้ถูกใช้งานแล้ว', null);
}

try {
    $sql = "UPDATE tbl_user SET
                user_prefix = :prefix,
                user_firstname = :firstname,
                user_lastname = :lastname,
                user_email = :email,
                user_phone = :phone,
                user_citizen_id = :citizen_id,
                user_cpd_no = :cpd_no,
                user_cpa_no = :cpa_no";
    $params = [
        ':prefix'     => $prefix,
        ':firstname'  => $firstname,
        ':lastname'   => $lastname,
        ':email'      => $email,
        ':phone'      => $phone,
        ':citizen_id' => $citizen_id,
        ':cpd_no'     => $cpd_no,
        ':cpa_no'     => $cpa_no,
        ':id'         => $target_id,
    ];

    if ($change_password) {
        $sql .= ", user_password = :password";
        $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    $sql .= " WHERE user_id = :id AND delete_at IS NULL";

    $stmt = $pdo_connect->prepare($sql);
    $stmt->execute($params);
    $stmt->closeCursor();

    Response::json(1, 'บันทึกข้อมูลสำเร็จ', ['user_id' => $target_id]);
} catch (Exception $e) {
    error_log('Update User Error: ' . $e->getMessage());
    Response::json(0, 'เกิดข้อผิดพลาดในการบันทึกข้อมูล', null);
} finally {
    $pdo_connect = null;
}
