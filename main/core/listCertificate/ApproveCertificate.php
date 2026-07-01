<?php
// อนุมัติออกใบรับรองการสอบ -> ตั้ง enroll_is_completed='1' (ผ่าน/จบหลักสูตร) + enroll_completed_at=NOW()
// เงื่อนไข: ต้องสอบผ่านจริง (attempt_pass=1) และยังไม่เคยอนุมัติมาก่อน

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

// ตรวจว่ามีรายการจริง + สอบผ่าน (attempt_pass ครั้งล่าสุด = 1) + ยังไม่อนุมัติ
$chk = $pdo_connect->prepare(
    "SELECT e.enroll_is_completed,
            (SELECT a.attempt_pass FROM tbl_exam_attempt a
              WHERE a.attempt_user_id = e.enroll_user_id AND a.attempt_course_id = e.enroll_course_id
              ORDER BY a.attempt_id DESC LIMIT 1) AS pass
       FROM tbl_course_enrollment e
      WHERE e.enroll_id = :id AND e.delete_at IS NULL LIMIT 1"
);
$chk->execute([':id' => $enroll_id]);
$row = $chk->fetch(PDO::FETCH_ASSOC);
$chk->closeCursor();

if (!$row) {
    Response::json(0, 'ไม่พบรายการนี้ หรือถูกยกเลิกไปแล้ว', null);
}
if ((string) ($row['pass'] ?? '0') !== '1') {
    Response::json(0, 'รายการนี้ยังสอบไม่ผ่าน ไม่สามารถอนุมัติออกใบรับรองได้', null);
}
if ((string) ($row['enroll_is_completed'] ?? '0') === '1') {
    Response::json(0, 'รายการนี้อนุมัติออกใบรับรองไปแล้ว', null);
}

$upd = $pdo_connect->prepare(
    "UPDATE tbl_course_enrollment
        SET enroll_is_completed = '1', enroll_completed_at = NOW()
      WHERE enroll_id = :id AND delete_at IS NULL"
);
$upd->execute([':id' => $enroll_id]);

Response::json(1, 'อนุมัติออกใบรับรองเรียบร้อยแล้ว', ['enroll_id' => $enroll_id]);
