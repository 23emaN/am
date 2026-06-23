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

$db_instance = new Connection();
$pdo_connect = $db_instance->getPdo();

if (!$pdo_connect) {
    Response::json(0, 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', null);
}

// ข้อมูลผู้ใช้ (เฉพาะที่ยังไม่ถูกลบ)
$stmt = $pdo_connect->prepare(
    "SELECT user_id, user_prefix, user_firstname, user_lastname, user_email,
            user_phone, user_citizen_id, user_cpd_no, user_cpa_no, user_status
     FROM tbl_user WHERE user_id = :id AND delete_at IS NULL LIMIT 1"
);
$stmt->execute([':id' => $target_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

if (!$user) {
    Response::json(0, 'ไม่พบผู้ใช้นี้', null);
}

// แท็บ "สิทธิ์เข้าคอร์สเรียน" — tbl_course_enrollment
$stmt_enroll = $pdo_connect->prepare(
    "SELECT e.enroll_id, c.course_name,
            c.course_code_cpd_1, c.course_code_cpa_1
     FROM tbl_course_enrollment e
     LEFT JOIN tbl_course c ON e.enroll_course_id = c.course_id
     WHERE e.enroll_user_id = :id AND e.delete_at IS NULL
     ORDER BY e.enroll_id DESC"
);
$stmt_enroll->execute([':id' => $target_id]);
$enroll_rows = $stmt_enroll->fetchAll(PDO::FETCH_ASSOC);
$stmt_enroll->closeCursor();

$enrollments = [];
foreach ($enroll_rows as $r) {
    $cpd = trim(str_replace(['[', ']'], '', (string)($r['course_code_cpd_1'] ?? '')));
    $cpa = trim(str_replace(['[', ']'], '', (string)($r['course_code_cpa_1'] ?? '')));
    $sku_parts = [];
    if ($cpd !== '') { $sku_parts[] = 'CPD: ' . $cpd; }
    if ($cpa !== '') { $sku_parts[] = 'CPA: ' . $cpa; }
    $enrollments[] = [
        'course_name' => $r['course_name'] ?? null,
        'sku'         => count($sku_parts) > 0 ? implode(' / ', $sku_parts) : null,
    ];
}

// แท็บ "ประวัติการสอบ/ใบรับรอง" — tbl_exam_attempt
$stmt_exam = $pdo_connect->prepare(
    "SELECT a.attempt_id, a.attempt_score, a.attempt_pass, c.course_name
     FROM tbl_exam_attempt a
     LEFT JOIN tbl_course c ON a.attempt_course_id = c.course_id
     WHERE a.attempt_user_id = :id
     ORDER BY a.attempt_id DESC"
);
$stmt_exam->execute([':id' => $target_id]);
$exam_rows = $stmt_exam->fetchAll(PDO::FETCH_ASSOC);
$stmt_exam->closeCursor();

$exams = [];
foreach ($exam_rows as $r) {
    $exams[] = [
        'course_name' => $r['course_name'] ?? null,
        'score'       => $r['attempt_score'] ?? null,
        'pass'        => (string)($r['attempt_pass'] ?? '0'),
    ];
}

Response::json(1, 'Success', [
    'user'        => $user,
    'enrollments' => $enrollments,
    'exams'       => $exams,
]);
