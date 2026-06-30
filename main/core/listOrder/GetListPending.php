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

// กัน output แปลกปลอมปน JSON -> DataTables ค้าง
ob_start();

/* ===== DataTables server-side: คำสั่งซื้อรอยืนยัน =====
   ขอบเขตคงที่: payment_status='0' (รอชำระเงิน) AND payment_method='2' (โอนเงินผ่านธนาคาร) */
$draw   = (int) ($_POST['draw'] ?? 1);
$start  = max(0, (int) ($_POST['start'] ?? 0));
$length = (int) ($_POST['length'] ?? 10);
if ($length < 1)   { $length = 10; }
if ($length > 100) { $length = 100; }

// ฟิลเตอร์จากฟอร์มด้านบน (สถานะ/วิธีชำระถูกบังคับไว้แล้ว ไม่รับจากผู้ใช้)
$f_order    = trim((string) ($_POST['f_order'] ?? ''));     // หมายเลขคำสั่งซื้อ (transaction_ref)
$f_customer = trim((string) ($_POST['f_customer'] ?? ''));  // ชื่อลูกค้า
$f_date     = trim((string) ($_POST['f_date'] ?? ''));      // วันที่สั่งซื้อ (Y-m-d หรือ d/m/Y)

// คอลัมน์ที่เรียงได้ (index ตรงกับ JS ในหน้า order_pending.php)
$order_cols = [
    1 => 'u.user_firstname',
    3 => 'o.total_price',
    4 => 'o.created_at',
];
$order_idx = (int) ($_POST['order'][0]['column'] ?? 4);
$order_col = $order_cols[$order_idx] ?? 'o.created_at';
$order_dir = strtolower((string) ($_POST['order'][0]['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

$joins = "FROM tbl_orders o
          LEFT JOIN tbl_user u ON o.user_id = u.user_id";

// เงื่อนไขฐาน (บังคับเสมอ) + ฟิลเตอร์ของผู้ใช้
$where  = ["o.payment_status = '0'", "o.payment_method = '2'"];
$params = [];
if ($f_order !== '') {
    $where[] = "o.transaction_ref LIKE :f_order";
    $params[':f_order'] = '%' . $f_order . '%';
}
if ($f_customer !== '') {
    $where[] = "CONCAT_WS(' ', u.user_firstname, u.user_lastname) LIKE :f_customer";
    $params[':f_customer'] = '%' . $f_customer . '%';
}
if ($f_date !== '') {
    // รองรับ d/m/Y -> Y-m-d
    $d = $f_date;
    if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $f_date, $m)) {
        $d = $m[3] . '-' . $m[2] . '-' . $m[1];
    }
    $where[] = "DATE(o.created_at) = :f_date";
    $params[':f_date'] = $d;
}
$where_sql = 'WHERE ' . implode(' AND ', $where);

// เงื่อนไขฐานสำหรับนับยอดรวมทั้งหมด (ก่อนกรองด้วยฟอร์ม)
$base_sql = "WHERE o.payment_status = '0' AND o.payment_method = '2'";

try {

// จำนวนทั้งหมด (เฉพาะออเดอร์รอยืนยันโอนเงิน)
$records_total = (int) $pdo_connect->query("SELECT COUNT(*) $joins $base_sql")->fetchColumn();

// จำนวนหลังกรอง
$stmt_cnt = $pdo_connect->prepare("SELECT COUNT(*) $joins $where_sql");
$stmt_cnt->execute($params);
$records_filtered = (int) $stmt_cnt->fetchColumn();
$stmt_cnt->closeCursor();

// ข้อมูลหน้าปัจจุบัน (ดึงชื่อคอร์สด้วย subquery GROUP_CONCAT กันแถวซ้ำจากการ join detail)
$sql = "SELECT o.order_id, o.transaction_ref, o.total_price, o.created_at,
               u.user_firstname, u.user_lastname,
               (SELECT GROUP_CONCAT(c.course_name SEPARATOR '\n')
                  FROM tbl_order_detail od
                  LEFT JOIN tbl_course c ON c.course_id = od.course_id
                 WHERE od.order_id = o.order_id) AS course_names
        $joins
        $where_sql
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
    $full = trim(($r['user_firstname'] ?? '') . ' ' . ($r['user_lastname'] ?? ''));
    $oid  = (int) $r['order_id'];

    // ชื่อคอร์ส (อาจหลายรายการ -> ขึ้นบรรทัดใหม่)
    $courses = trim((string) ($r['course_names'] ?? ''));
    $course_html = $courses !== ''
        ? implode('<br>', array_map($esc, explode("\n", $courses)))
        : '<span class="text-muted">-</span>';

    $action = '<a href="order_detail.php?id=' . $oid . '" class="btn btn-sm btn-info text-white">ดูรายละเอียด</a> '
            . '<button type="button" class="btn btn-sm btn-danger" onclick="CancelOrderRow(' . $oid . ')">ยกเลิกคำสั่งซื้อ</button>';

    $data[] = [
        'no'       => $start + (++$i),
        'customer' => $esc($full !== '' ? $full : '-'),
        'courses'  => $course_html,
        'total'    => number_format((float) ($r['total_price'] ?? 0), 2) . ' บาท',
        'created'  => $r['created_at'] ? date('d/m/Y H:i', strtotime($r['created_at'])) : '-',
        'action'   => $action,
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
    error_log('GetListPending Error: ' . $e->getMessage());
    if (ob_get_length() !== false) { ob_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => 0,
        'recordsFiltered' => 0,
        'data'            => [],
        'error'           => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
