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

// จับ output ที่อาจหลุดมา (warning/notice) ไม่ให้ปน JSON -> กัน DataTables ค้าง
ob_start();

/* ===== DataTables server-side processing =====
   ตารางใหญ่ (หลักหมื่นแถว+โตเรื่อย ๆ) จึงโหลดทีละหน้าแทน client-side
   ตอบกลับด้วยรูปแบบของ DataTables โดยตรง: { draw, recordsTotal, recordsFiltered, data } */

$draw   = (int) ($_POST['draw'] ?? 1);
$start  = max(0, (int) ($_POST['start'] ?? 0));
$length = (int) ($_POST['length'] ?? 10);
if ($length < 1)   { $length = 10; }   // กันค่าแปลก / -1
if ($length > 100) { $length = 100; }  // เพดานกันโหลดหนัก
$search = trim((string) ($_POST['search']['value'] ?? ''));

// คอลัมน์ที่เรียงได้ (index ตรงกับ columns ใน JS)
$order_cols = [
    0 => 'l.log_id',
    1 => 'u.user_firstname',
    2 => 'u.user_citizen_id',
    3 => 'l.remark',
    4 => 'l.action_type',
    5 => 'a.user_firstname',
];
$order_idx = (int) ($_POST['order'][0]['column'] ?? 0);
$order_col = $order_cols[$order_idx] ?? 'l.log_id';
$order_dir = strtolower((string) ($_POST['order'][0]['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

$joins = "FROM tbl_identity_verification_log l
          LEFT JOIN tbl_user u ON l.user_id = u.user_id
          LEFT JOIN tbl_user a ON l.create_user_id = a.user_id";

$where  = '';
$params = [];
if ($search !== '') {
    // ใช้ placeholder แยกกัน (:s1/:s2/:s3) — PDO ห้ามใช้ชื่อซ้ำเมื่อปิด emulate prepares
    $where = "WHERE (CONCAT_WS(' ', u.user_firstname, u.user_lastname) LIKE :s1
                  OR u.user_citizen_id LIKE :s2
                  OR l.remark LIKE :s3)";
    $like = '%' . $search . '%';
    $params = [':s1' => $like, ':s2' => $like, ':s3' => $like];
}

// จำนวนทั้งหมด (ไม่กรอง)
$records_total = (int) $pdo_connect->query("SELECT COUNT(*) FROM tbl_identity_verification_log")->fetchColumn();

// จำนวนหลังกรอง
if ($search !== '') {
    $stmt_cnt = $pdo_connect->prepare("SELECT COUNT(*) $joins $where");
    $stmt_cnt->execute($params);
    $records_filtered = (int) $stmt_cnt->fetchColumn();
    $stmt_cnt->closeCursor();
} else {
    $records_filtered = $records_total;
}

// ข้อมูลหน้าปัจจุบัน
$sql = "SELECT l.action_type, l.remark,
               u.user_firstname, u.user_lastname, u.user_citizen_id,
               a.user_firstname AS admin_firstname, a.user_lastname AS admin_lastname
        $joins
        $where
        ORDER BY $order_col $order_dir
        LIMIT :start, :length";
$stmt = $pdo_connect->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':length', $length, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

$esc = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$data = [];
$i = 0;
foreach ($rows as $r) {
    $full  = trim(($r['user_firstname'] ?? '') . ' ' . ($r['user_lastname'] ?? ''));
    $admin = trim(($r['admin_firstname'] ?? '') . ' ' . ($r['admin_lastname'] ?? ''));
    $act   = (string) ($r['action_type'] ?? '0');

    if ($act === '1') {
        $status = '<span class="badge bg-success">ผ่านแล้ว</span>';
    } elseif ($act === '2') {
        $status = '<span class="badge bg-danger">ยกเลิกการยืนยัน</span>';
    } else {
        $status = '<span class="text-muted">-</span>';
    }

    $data[] = [
        'no'         => $start + (++$i),
        'full_name'  => $esc($full !== '' ? $full : '-'),
        'citizen_id' => $esc($r['user_citizen_id'] ?? '-'),
        'remark'     => $esc($r['remark'] ?? '-'),
        'status'     => $status,
        'admin_name' => $esc($admin !== '' ? $admin : 'ไม่มีข้อมูล'),
    ];
}

if (ob_get_length() !== false) {
    ob_clean(); // ทิ้ง output แปลกปลอม เหลือแต่ JSON สะอาด
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $records_total,
    'recordsFiltered' => $records_filtered,
    'data'            => $data,
], JSON_UNESCAPED_UNICODE);
exit;
