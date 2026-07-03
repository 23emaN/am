<?php
// หน้าแรก (dashboard) — สถิติ + ยอดขายรายวัน ตามช่วงวันที่
// สมาชิกใหม่: tbl_user.create_at / คำสั่งซื้อใหม่: tbl_orders.created_at
// ยอดเงิน + กราฟ: tbl_orders ที่ payment_status='1' (จ่ายแล้ว)

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

// แปลง d/m/Y (หรือ Y-m-d) -> Y-m-d
$parse = function ($d) {
    $d = trim((string) $d);
    if ($d === '') { return null; }
    if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $d, $m)) { return $m[3] . '-' . $m[2] . '-' . $m[1]; }
    if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $d)) { return $d; }
    return null;
};
$from = $parse($_POST['date_from'] ?? '') ?: date('Y-m-d', strtotime('-29 days'));
$to   = $parse($_POST['date_to']   ?? '') ?: date('Y-m-d');
if ($from > $to) { [$from, $to] = [$to, $from]; }

try {
    // สมาชิกใหม่ (สมัครในช่วง)
    $st = $pdo_connect->prepare("SELECT COUNT(*) FROM tbl_user WHERE delete_at IS NULL AND DATE(create_at) BETWEEN :f AND :t");
    $st->execute([':f' => $from, ':t' => $to]);
    $new_members = (int) $st->fetchColumn();
    $st->closeCursor();

    // คำสั่งซื้อใหม่ (ทั้งหมดในช่วง)
    $st = $pdo_connect->prepare("SELECT COUNT(*) FROM tbl_orders WHERE DATE(created_at) BETWEEN :f AND :t");
    $st->execute([':f' => $from, ':t' => $to]);
    $new_orders = (int) $st->fetchColumn();
    $st->closeCursor();

    // ยอดเงิน (เฉพาะจ่ายแล้ว)
    $st = $pdo_connect->prepare("SELECT COALESCE(SUM(total_price),0) FROM tbl_orders WHERE payment_status = '1' AND DATE(created_at) BETWEEN :f AND :t");
    $st->execute([':f' => $from, ':t' => $to]);
    $revenue = (float) $st->fetchColumn();
    $st->closeCursor();

    // ยอดขายรายวัน (จ่ายแล้ว)
    $st = $pdo_connect->prepare("SELECT DATE(created_at) AS d, COALESCE(SUM(total_price),0) AS s
                                 FROM tbl_orders
                                 WHERE payment_status = '1' AND DATE(created_at) BETWEEN :f AND :t
                                 GROUP BY DATE(created_at)");
    $st->execute([':f' => $from, ':t' => $to]);
    $map = [];
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) { $map[$r['d']] = (float) $r['s']; }
    $st->closeCursor();

    // เติมวันให้ครบช่วง (กันช่องว่างในกราฟ) — จำกัดไม่เกิน 400 วันกันช่วงยาวผิดปกติ
    $days = [];
    $sales = [];
    $cur = strtotime($from);
    $end = strtotime($to);
    $guard = 0;
    while ($cur <= $end && $guard < 400) {
        $ymd = date('Y-m-d', $cur);
        $days[]  = date('d/m', $cur);
        $sales[] = $map[$ymd] ?? 0;
        $cur = strtotime('+1 day', $cur);
        $guard++;
    }

    // ---- แนวโน้มเทียบช่วงก่อนหน้า (ความยาวเท่ากัน ก่อน [from,to] ทันที) ----
    $len_days  = (int) floor((strtotime($to) - strtotime($from)) / 86400) + 1;
    $prev_to   = date('Y-m-d', strtotime($from . ' -1 day'));
    $prev_from = date('Y-m-d', strtotime($from . ' -' . $len_days . ' day'));

    $st = $pdo_connect->prepare("SELECT COUNT(*) FROM tbl_user WHERE delete_at IS NULL AND DATE(create_at) BETWEEN :f AND :t");
    $st->execute([':f' => $prev_from, ':t' => $prev_to]);
    $prev_members = (int) $st->fetchColumn();
    $st->closeCursor();

    $st = $pdo_connect->prepare("SELECT COUNT(*) FROM tbl_orders WHERE DATE(created_at) BETWEEN :f AND :t");
    $st->execute([':f' => $prev_from, ':t' => $prev_to]);
    $prev_orders = (int) $st->fetchColumn();
    $st->closeCursor();

    $st = $pdo_connect->prepare("SELECT COALESCE(SUM(total_price),0) FROM tbl_orders WHERE payment_status = '1' AND DATE(created_at) BETWEEN :f AND :t");
    $st->execute([':f' => $prev_from, ':t' => $prev_to]);
    $prev_revenue = (float) $st->fetchColumn();
    $st->closeCursor();

    // % เปลี่ยนแปลง + ทิศทาง + diff (จำนวนที่เพิ่ม/ลดจริง)
    // กันหารศูนย์: ไม่มีฐานเทียบ (prev<=0) -> pct=null แต่ยังส่ง diff ให้แสดงจำนวนจริงแทน %
    $trend = function ($cur, $prev) {
        $cur = (float) $cur; $prev = (float) $prev;
        $diff = $cur - $prev;
        if ($prev <= 0) { return ['pct' => null, 'dir' => ($cur > 0 ? 'up' : 'flat'), 'diff' => $diff]; }
        $pct = ($diff / $prev) * 100;
        return ['pct' => round(abs($pct), 1), 'dir' => ($pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'flat')), 'diff' => $diff];
    };
    Response::json(1, 'สำเร็จ', [
        'new_members' => $new_members,
        'new_orders'  => $new_orders,
        'revenue'     => $revenue,
        'days'        => $days,
        'sales'       => $sales,
        'trend'       => [
            'members' => $trend($new_members, $prev_members),
            'orders'  => $trend($new_orders,  $prev_orders),
            'revenue' => $trend($revenue,     $prev_revenue),
        ],
    ]);

} catch (\Throwable $e) {
    error_log('GetDashboard Error: ' . $e->getMessage());
    Response::json(0, 'เกิดข้อผิดพลาด: ' . $e->getMessage(), null);
}
