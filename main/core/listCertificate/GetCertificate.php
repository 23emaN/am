<?php
// ดึงข้อมูลใบรับรองผลการสอบ 1 ใบ (สำหรับหน้าพิมพ์ + โมดัลดูรูปยืนยันตัวตน)

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

$stmt = $pdo_connect->prepare(
    "SELECT e.enroll_id, e.enroll_user_id, e.enroll_course_id, e.enroll_is_completed, e.create_at,
            u.user_firstname, u.user_lastname, u.user_citizen_id, u.user_cpd_no, u.user_cpa_no,
            u.id_card_image, u.current_photo, u.identity_verified,
            c.course_name, c.course_instructor, c.course_code_cpd_1, c.course_code_cpa_1,
            c.course_approval_date_1, c.course_cpd_hour, c.course_cpd_ethics, c.course_cpd_other,
            c.course_number_exam,
            (SELECT a.attempt_score FROM tbl_exam_attempt a
              WHERE a.attempt_user_id = e.enroll_user_id AND a.attempt_course_id = e.enroll_course_id
              ORDER BY a.attempt_id DESC LIMIT 1) AS score
     FROM tbl_course_enrollment e
     LEFT JOIN tbl_user u   ON e.enroll_user_id = u.user_id
     LEFT JOIN tbl_course c ON e.enroll_course_id = c.course_id
     WHERE e.enroll_id = :id AND e.delete_at IS NULL LIMIT 1"
);
$stmt->execute([':id' => $enroll_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

if (!$row) {
    Response::json(0, 'ไม่พบรายการนี้ หรือถูกยกเลิกไปแล้ว', null);
}

// รูป KYC เก็บอยู่ในโปรเจกต์ลูกค้า (cpdth) ซึ่งเป็นโฟลเดอร์พี่น้อง -> base ../../cpdth/
$img = function ($p) {
    $p = trim((string) ($p ?? ''));
    return $p !== '' ? ('../../cpdth/' . ltrim($p, '/')) : '';
};

$ts = $row['create_at'] ? strtotime($row['create_at']) : time();

// คะแนน + เปอร์เซ็นต์
$total = (int) ($row['course_number_exam'] ?? 0);
$score = $row['score'] !== null ? (int) $row['score'] : null;
$percent = ($score !== null && $total > 0) ? number_format($score / $total * 100, 2) : null;

$fmt_date = function ($d) {
    if (!$d || $d === '0000-00-00') { return ''; }
    return date('d/m/Y', strtotime($d));
};

Response::json(1, 'Success', [
    'enroll_id'      => (int) $row['enroll_id'],
    'cert_no'        => date('ym', $ts) . str_pad((string) $row['enroll_id'], 4, '0', STR_PAD_LEFT),
    'fullname'       => trim(($row['user_firstname'] ?? '') . ' ' . ($row['user_lastname'] ?? '')),
    'accountant_no'  => (string) ($row['user_citizen_id'] ?? ($row['user_cpd_no'] ?? '')),
    'course_name'    => (string) ($row['course_name'] ?? ''),
    'course_code'    => (string) ($row['course_code_cpd_1'] ?? ($row['course_code_cpa_1'] ?? '')),
    'approval_date'  => $fmt_date($row['course_approval_date_1'] ?? ''),
    'instructor'     => (string) ($row['course_instructor'] ?? ''),
    'cpd_hour'       => number_format((float) ($row['course_cpd_hour'] ?? 0), 2),
    'cpd_ethics'     => number_format((float) ($row['course_cpd_ethics'] ?? 0), 2),
    'cpd_other'      => number_format((float) ($row['course_cpd_other'] ?? 0), 2),
    'train_date'     => date('d/m/Y', $ts),
    'score'          => $score,
    'percent'        => $percent,
    'passed'         => ((string) ($row['enroll_is_completed'] ?? '0') === '1') ? 1 : 0,
    'approved'       => ((string) ($row['identity_verified'] ?? '0') === '2') ? 1 : 0,
    'id_card_image'  => $img($row['id_card_image'] ?? ''),
    'current_photo'  => $img($row['current_photo'] ?? ''),
]);
