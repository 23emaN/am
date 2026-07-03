<?php
// ใบรับรองผลการสอบ — ดึงรายการแบบ custom table (แบ่งหน้าฝั่ง server)
// ผ่าน/ไม่ผ่าน จาก tbl_exam_attempt.attempt_pass (ครั้งล่าสุด)
// การอนุมัติ จาก tbl_course_enrollment.enroll_is_completed (จบ/ผ่านหลักสูตร)
// คืน JSON { list, total, page, per_page } -> หน้า course_certificate นำไป render ผ่าน view/listCertificate/ViewData.php

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

$page     = max(1, (int) ($_POST['page'] ?? 1));
$per_page = 10;
$offset   = ($page - 1) * $per_page;

$f_course  = trim((string) ($_POST['f_course'] ?? ''));   // enroll_course_id
$f_member  = trim((string) ($_POST['f_member'] ?? ''));   // enroll_user_id
$f_status  = trim((string) ($_POST['f_status'] ?? ''));   // 1=ผ่าน 0=ไม่ผ่าน
$f_approve = trim((string) ($_POST['f_approve'] ?? ''));  // 1=อนุมัติ 0=รออนุมัติ
$search    = trim((string) ($_POST['search'] ?? ''));     // ชื่อผู้สอบ / คอร์ส / เลขบัตรประชาชน

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
if ($f_approve === '1' || $f_approve === '0')   { $where[] = "e.enroll_is_completed = :f_approve"; $params[':f_approve'] = $f_approve; }
if ($search !== '') {
    $where[] = "(CONCAT_WS(' ', u.user_firstname, u.user_lastname) LIKE :search
                 OR c.course_name LIKE :search
                 OR u.user_citizen_id LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
$where_sql = 'WHERE ' . implode(' AND ', $where);

try {
    // จำนวนทั้งหมดหลังกรอง
    $stmt_cnt = $pdo_connect->prepare("SELECT COUNT(*) $joins $where_sql");
    $stmt_cnt->execute($params);
    $total = (int) $stmt_cnt->fetchColumn();
    $stmt_cnt->closeCursor();

    // ข้อมูลหน้าปัจจุบัน (เรียงคงที่ ใหม่สุดก่อน)
    $sql = "SELECT e.enroll_id, e.enroll_is_completed, e.create_at,
                   u.user_firstname, u.user_lastname,
                   c.course_name, c.course_number_exam,
                   (SELECT a.attempt_score FROM tbl_exam_attempt a
                     WHERE a.attempt_user_id = e.enroll_user_id AND a.attempt_course_id = e.enroll_course_id
                     ORDER BY a.attempt_id DESC LIMIT 1) AS score,
                   $pass_expr AS pass
            $joins
            $where_sql
            ORDER BY e.enroll_id DESC
            LIMIT :offset, :per_page";
    $stmt = $pdo_connect->prepare($sql);
    foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    $list = [];
    foreach ($rows as $r) {
        $full    = trim(($r['user_firstname'] ?? '') . ' ' . ($r['user_lastname'] ?? ''));
        $ts      = $r['create_at'] ? strtotime($r['create_at']) : time();
        $total_q = (int) ($r['course_number_exam'] ?? 0);
        $score   = $r['score'] !== null ? (int) $r['score'] : null;
        $percent = ($score !== null && $total_q > 0) ? number_format($score / $total_q * 100, 2) : null;

        $list[] = [
            'enroll_id' => (int) $r['enroll_id'],
            'cert_no'   => date('ym', $ts) . str_pad((string) $r['enroll_id'], 4, '0', STR_PAD_LEFT),
            'course'    => $r['course_name'] ?? '',
            'examiner'  => $full !== '' ? $full : '',
            'score'     => $score,
            'percent'   => $percent,
            'passed'    => ((string) ($r['pass'] ?? '0') === '1') ? 1 : 0,
            'approved'  => ((string) ($r['enroll_is_completed'] ?? '0') === '1') ? 1 : 0,
        ];
    }

    Response::json(1, 'สำเร็จ', ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page]);

} catch (\Throwable $e) {
    error_log('GetListCertificate Error: ' . $e->getMessage());
    Response::json(0, 'เกิดข้อผิดพลาด: ' . $e->getMessage(), null);
}
