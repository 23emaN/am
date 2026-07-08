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
    Response::json(0, 'ไม่พบรหัสผู้ใช้', null);
}

// ผลการตรวจสอบเอกสาร: 2 = อนุมัติ, 1 = ปฏิเสธ
$result = isset($_POST['approver_citizen']) ? trim((string) $_POST['approver_citizen']) : '';
if (!in_array($result, ['1', '2'], true)) {
    Response::json(0, 'กรุณาเลือกผลการตรวจสอบเอกสาร', null);
}

$remark = isset($_POST['remark']) ? trim((string) $_POST['remark']) : '';

// ปฏิเสธ (1) ต้องมีหมายเหตุเสมอ
if ($result === '1' && $remark === '') {
    Response::json(0, 'กรุณาระบุหมายเหตุการไม่อนุมัติ', null);
}

$db_instance = new Connection();
$pdo_connect = $db_instance->getPdo();

if (!$pdo_connect) {
    Response::json(0, 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', null);
}

// ตรวจว่ามีผู้ใช้นี้และอยู่ระหว่างดำเนินการจริง
$stmt_check = $pdo_connect->prepare(
    "SELECT user_id FROM tbl_user WHERE user_id = :id AND delete_at IS NULL LIMIT 1"
);
$stmt_check->execute([':id' => $target_id]);
$exists = $stmt_check->fetch(PDO::FETCH_ASSOC);
$stmt_check->closeCursor();

if (!$exists) {
    Response::json(0, 'ไม่พบคำขอยืนยันตัวตนนี้', null);
}

// อนุมัติ -> ยืนยันตัวตนสำเร็จ (identity_verified = 2)
// ปฏิเสธ -> กลับไปสถานะยังไม่ยืนยัน (identity_verified = 0) ให้ผู้ใช้ส่งใหม่ได้
$identity_verified = ($result === '2') ? '2' : '0';

// log ประวัติการยืนยันตัวตน (action_type: 1 = ผ่าน/อนุมัติ, 2 = ยกเลิก/ปฏิเสธ)
$action_type = ($result === '2') ? '1' : '2';
$log_remark  = ($result === '2')
    ? ($remark !== '' ? $remark : 'อนุมัติยืนยันตัวตนโดยตรวจสอบเอกสาร')
    : $remark;   // ปฏิเสธ มีหมายเหตุเสมอ (ตรวจไว้ด้านบนแล้ว)

try {
    $pdo_connect->beginTransaction();

    $stmt = $pdo_connect->prepare(
        "UPDATE tbl_user
            SET identity_verified = :iv,
                approver_citizen  = :ac,
                remark            = :rm
          WHERE user_id = :id AND delete_at IS NULL"
    );
    $stmt->execute([
        ':iv' => $identity_verified,
        ':ac' => $result,
        ':rm' => $remark !== '' ? $remark : null,
        ':id' => $target_id,
    ]);
    $stmt->closeCursor();

    // บันทึกประวัติ -> ให้แท็บ "ประวัติการยืนยันตัวตน" มีข้อมูล
    $stmt_log = $pdo_connect->prepare(
        "INSERT INTO tbl_identity_verification_log (user_id, create_user_id, action_type, remark)
         VALUES (:uid, :admin, :act, :rm)"
    );
    $stmt_log->execute([
        ':uid'   => $target_id,
        ':admin' => (int) $admin_id,
        ':act'   => $action_type,
        ':rm'    => $log_remark !== '' ? $log_remark : null,
    ]);
    $stmt_log->closeCursor();

    $pdo_connect->commit();
} catch (\Throwable $e) {
    if ($pdo_connect->inTransaction()) { $pdo_connect->rollBack(); }
    error_log('UpdateVerifyRequest Error: ' . $e->getMessage());
    Response::json(0, 'บันทึกผลการตรวจสอบไม่สำเร็จ', null);
}

$msg = ($result === '2') ? 'อนุมัติการยืนยันตัวตนเรียบร้อย' : 'ปฏิเสธการยืนยันตัวตนเรียบร้อย';
Response::json(1, $msg, null);
