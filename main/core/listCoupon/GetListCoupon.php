<?php

// คูปองส่วนลด — ดึงรายการแบบ custom table (แบ่งหน้าฝั่ง server ด้วย LIMIT/OFFSET)
// คืน JSON { list, total, page, per_page } -> หน้า coupon นำไป render ผ่าน view/listCoupon/GetTable.php

use App\Utility\Auth;
use App\Utility\Response;
use App\Database\Connection;

$access_token = Auth::requireUserToken();
$user_id = $access_token->user_id ?? null;

if (!$user_id) {
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

$search = trim((string) ($_POST['search'] ?? ''));   // ค้นหาจาก code / รายละเอียด

$where  = ["delete_at IS NULL"];
$params = [];
if ($search !== '') {
    $where[] = "(CONCAT_WS(' ', coupon_code, coupon_detail) LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
$where_sql = 'WHERE ' . implode(' AND ', $where);

try {
    // จำนวนทั้งหมดหลังกรอง/ค้นหา
    $stmt_cnt = $pdo_connect->prepare("SELECT COUNT(*) FROM tbl_coupon $where_sql");
    $stmt_cnt->execute($params);
    $total = (int) $stmt_cnt->fetchColumn();
    $stmt_cnt->closeCursor();

    // ข้อมูลหน้าปัจจุบัน
    $sql_data = "SELECT
                    coupon_id, coupon_code, coupon_detail, coupon_type, coupon_no,
                    coupon_status, coupon_limit, coupon_limit_person,
                    coupon_min, coupon_max, coupon_start, coupon_end
                FROM tbl_coupon
                $where_sql
                ORDER BY coupon_id DESC
                LIMIT :offset, :per_page";

    $stmt_data = $pdo_connect->prepare($sql_data);
    foreach ($params as $k => $v) { $stmt_data->bindValue($k, $v); }
    $stmt_data->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt_data->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    $stmt_data->execute();
    $result_data = $stmt_data->fetchAll(PDO::FETCH_ASSOC);
    $stmt_data->closeCursor();

    Response::json(1, 'สำเร็จ', [
        'list'     => $result_data,
        'total'    => $total,
        'page'     => $page,
        'per_page' => $per_page,
    ]);

} catch (\Throwable $e) {
    error_log('GetListCoupon Error: ' . $e->getMessage());
    Response::json(0, 'เกิดข้อผิดพลาด', null);
}
