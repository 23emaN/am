<?php
// ใบรับรองผลการสอบ — DataTables server-side
// ผ่าน/ไม่ผ่าน ดูจาก tbl_exam_attempt.attempt_pass (ครั้งล่าสุด, 1=ผ่าน)
// คะแนนจาก tbl_exam_attempt (ครั้งล่าสุด) / การอนุมัติจาก tbl_course_enrollment.enroll_access (1=อนุมัติ)

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

ob_start();

$draw   = (int) ($_POST['draw'] ?? 1);
$start  = max(0, (int) ($_POST['start'] ?? 0));
$length = (int) ($_POST['length'] ?? 10);
if ($length < 1)   { $length = 10; }
if ($length > 100) { $length = 100; }

$f_course  = trim((string) ($_POST['f_course'] ?? ''));   // enroll_course_id
$f_member  = trim((string) ($_POST['f_member'] ?? ''));   // enroll_user_id
$f_status  = trim((string) ($_POST['f_status'] ?? ''));   // 1=ผ่าน 0=ไม่ผ่าน
$f_approve = trim((string) ($_POST['f_approve'] ?? ''));  // 1=อนุมัติ 0=รออนุมัติ

$joins = "FROM tbl_course_enrollment e
          LEFT JOIN tbl_user u   ON e.enroll_user_id = u.user_id
          LEFT JOIN tbl_course c ON e.enroll_course_id = c.course_id";

// ผลสอบครั้งล่าสุดของ (ผู้เรียน, คอร์ส) นั้น ๆ
$pass_expr = "(SELECT a.attempt_pass FROM tbl_exam_attempt a
               WHERE a.attempt_user_id = e.enroll_user_id AND a.attempt_course_id = e.enroll_course_id
               ORDER BY a.attempt_id DESC LIMIT 1)";

$where  = ["e.delete_at IS NULL"];
$params = [];
if ($f_course !== '' && ctype_digit($f_course)) { $where[] = "e.enroll_course_id = :f_course"; $params[':f_course'] = (int) $f_course; }
if ($f_member !== '' && ctype_digit($f_member)) { $where[] = "e.enroll_user_id = :f_member"; $params[':f_member'] = (int) $f_member; }
if ($f_status === '1' || $f_status === '0')     { $where[] = "COALESCE($pass_expr, '0') = :f_status"; $params[':f_status'] = $f_status; }
if ($f_approve === '1' || $f_approve === '0')   { $where[] = "e.enroll_access = :f_approve"; $params[':f_approve'] = $f_approve; }
$where_sql = 'WHERE ' . implode(' AND ', $where);

$records_total = (int) $pdo_connect->query("SELECT COUNT(*) FROM tbl_course_enrollment WHERE delete_at IS NULL")->fetchColumn();
if (count($where) > 1) {
    $stmt_cnt = $pdo_connect->prepare("SELECT COUNT(*) $joins $where_sql");
    $stmt_cnt->execute($params);
    $records_filtered = (int) $stmt_cnt->fetchColumn();
    $stmt_cnt->closeCursor();
} else {
    $records_filtered = $records_total;
}

try {
$sql = "SELECT e.enroll_id, e.enroll_user_id, e.enroll_course_id, e.enroll_access, e.create_at,
               u.user_firstname, u.user_lastname,
               c.course_name, c.course_number_exam,
               (SELECT a.attempt_score FROM tbl_exam_attempt a
                 WHERE a.attempt_user_id = e.enroll_user_id AND a.attempt_course_id = e.enroll_course_id
                 ORDER BY a.attempt_id DESC LIMIT 1) AS score,
               $pass_expr AS pass
        $joins
        $where_sql
        ORDER BY e.enroll_id DESC
        LIMIT :start, :length";
$stmt = $pdo_connect->prepare($sql);
foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':length', $length, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

$esc = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$data = [];
$i = 0;
foreach ($rows as $r) {
    $full = trim(($r['user_firstname'] ?? '') . ' ' . ($r['user_lastname'] ?? ''));
    $ts   = $r['create_at'] ? strtotime($r['create_at']) : time();

    // คะแนน + เปอร์เซ็นต์
    $total = (int) ($r['course_number_exam'] ?? 0);
    if ($r['score'] !== null) {
        $sc = (int) $r['score'];
        $pct = $total > 0 ? number_format($sc / $total * 100, 2) : '0.00';
        $score_txt = $sc . ' คะแนน / ' . $pct . ' %';
    } else {
        $score_txt = '<span class="text-muted">-</span>';
    }

    // สถานะการสอบ (จาก tbl_exam_attempt.attempt_pass ครั้งล่าสุด)
    $passed = ((string) ($r['pass'] ?? '0') === '1');
    $status = $passed
        ? '<span class="badge bg-success">ผ่าน</span>'
        : '<span class="badge bg-danger">ไม่ผ่าน</span>';

    // การอนุมัติ (จาก enroll_access = 1) — แสดงเฉพาะเมื่อสอบผ่าน
    $approved = ((string) ($r['enroll_access'] ?? '0') === '1');
    if ($passed) {
        $approve = $approved
            ? '<span class="badge bg-success">อนุมัติ</span>'
            : '<span class="badge bg-secondary">รออนุมัติ</span>';
    } else {
        $approve = '';
    }

    $data[] = [
        'enroll_id' => (int) $r['enroll_id'],
        'no'        => $start + (++$i),
        'cert_no'   => date('ym', $ts) . str_pad((string) $r['enroll_id'], 4, '0', STR_PAD_LEFT),
        'course'    => $esc($r['course_name'] ?? '-'),
        'examiner'  => $esc($full !== '' ? $full : '-'),
        'score'     => $score_txt,
        'status'    => $status,
        'approve'   => $approve,
        'passed'    => $passed ? 1 : 0,
        'approved'  => $approved ? 1 : 0,
    ];
}

if (ob_get_length() !== false) { ob_clean(); }
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['draw' => $draw, 'recordsTotal' => $records_total, 'recordsFiltered' => $records_filtered, 'data' => $data], JSON_UNESCAPED_UNICODE);
exit;

} catch (\Throwable $e) {
    error_log('GetListCertificate Error: ' . $e->getMessage());
    if (ob_get_length() !== false) { ob_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['draw' => $draw, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}
