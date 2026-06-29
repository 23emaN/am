<?php
// คอร์สเรียนคงเหลือในระบบ — DataTables server-side จาก tbl_course_enrollment

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

$f_course = trim((string) ($_POST['f_course'] ?? ''));   // enroll_course_id
$f_member = trim((string) ($_POST['f_member'] ?? ''));   // ชื่อสมาชิก

$order_cols = [
    0 => 'e.enroll_id',
    1 => 'u.user_firstname',
    2 => 'u.user_phone',
    3 => 'c.course_name',
    4 => 'e.create_at',
    5 => 'e.enroll_date',
    6 => 'e.enroll_expiry_date',
];
$order_idx = (int) ($_POST['order'][0]['column'] ?? 0);
$order_col = $order_cols[$order_idx] ?? 'e.enroll_id';
$order_dir = strtolower((string) ($_POST['order'][0]['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

$joins = "FROM tbl_course_enrollment e
          LEFT JOIN tbl_user u   ON e.enroll_user_id = u.user_id
          LEFT JOIN tbl_course c ON e.enroll_course_id = c.course_id";

$where  = ["e.delete_at IS NULL"];
$params = [];
if ($f_course !== '' && ctype_digit($f_course)) {
    $where[] = "e.enroll_course_id = :f_course";
    $params[':f_course'] = (int) $f_course;
}
if ($f_member !== '') {
    if (ctype_digit($f_member)) {
        $where[] = "e.enroll_user_id = :f_member";
        $params[':f_member'] = (int) $f_member;
    } else {
        $where[] = "CONCAT_WS(' ', u.user_firstname, u.user_lastname) LIKE :f_member";
        $params[':f_member'] = '%' . $f_member . '%';
    }
}
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
$sql = "SELECT e.enroll_id, e.enroll_payment_status, e.enroll_date, e.enroll_expiry_date,
               e.enroll_is_completed, e.create_at,
               u.user_firstname, u.user_lastname, u.user_phone,
               c.course_name, c.course_price, c.course_promotion
        $joins
        $where_sql
        ORDER BY $order_col $order_dir
        LIMIT :start, :length";
$stmt = $pdo_connect->prepare($sql);
foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':length', $length, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

$esc = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$fmt = fn($d) => $d ? date('d/m/Y H:i', strtotime($d)) : '-';
$now = time();

$data = [];
$i = 0;
foreach ($rows as $r) {
    $full  = trim(($r['user_firstname'] ?? '') . ' ' . ($r['user_lastname'] ?? ''));
    $buy   = $r['create_at'] ?: $r['enroll_date'];
    $open  = $r['enroll_date'] ?: $r['create_at'];
    $exp   = $r['enroll_expiry_date'] ?? null;

    // อายุคงเหลือ (วัน)
    if (!$exp) {
        $expiry_txt = '<span class="text-muted">ไม่มีกำหนด</span>';
        $remain_txt = '<span class="text-muted">-</span>';
    } else {
        $expiry_txt = $esc($fmt($exp));
        $days = (int) ceil((strtotime($exp) - $now) / 86400);
        $remain_txt = $days > 0
            ? '<span class="text-success">' . $days . ' วัน</span>'
            : '<span class="text-danger">หมดอายุ</span>';
    }

    // สถานะ (เรียนจบหรือยัง)
    $status = ((string) ($r['enroll_is_completed'] ?? '0') === '1')
        ? '<span class="badge bg-success">เรียนจบแล้ว</span>'
        : '<span class="badge bg-secondary">กำลังเรียน</span>';

    // ราคา (ใช้ราคาโปรถ้ามี ไม่งั้นราคาปกติ)
    $promo = (float) ($r['course_promotion'] ?? 0);
    $price = ($promo > 0) ? $promo : (float) ($r['course_price'] ?? 0);

    $data[] = [
        'enroll_id'  => (int) $r['enroll_id'],
        'no'         => $start + (++$i),
        'member'     => $esc($full !== '' ? $full : '-'),
        'phone'      => $esc($r['user_phone'] ?? '-'),
        'course'     => $esc($r['course_name'] ?? '-'),
        'buy_at'     => $esc($fmt($buy)),
        'open_at'    => $esc($fmt($open)),
        'expiry'     => $expiry_txt,
        'remain'     => $remain_txt,
        'status'     => $status,
        'price'      => number_format($price, 2) . ' ฿',
        'expiry_raw' => $exp ? date('d/m/Y', strtotime($exp)) : '',
        'is_completed' => (string) ($r['enroll_is_completed'] ?? '0'),
    ];
}

if (ob_get_length() !== false) { ob_clean(); }
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $records_total,
    'recordsFiltered' => $records_filtered,
    'data'            => $data,
], JSON_UNESCAPED_UNICODE);
exit;

} catch (\Throwable $e) {
    error_log('GetListEnrollment Error: ' . $e->getMessage());
    if (ob_get_length() !== false) { ob_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'draw' => $draw, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [],
        'error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
