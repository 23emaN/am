<?php
// รายการใบกำกับภาษี (E-Tax) — 1 ออเดอร์ที่ชำระแล้ว = 1 ใบกำกับภาษี
// ข้อมูลลูกค้า/ที่อยู่จาก tbl_user_address (ที่อยู่ default ของลูกค้า)
// เลขเอกสาร/สถานะ = ระบบเราออกเอง

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

// ออกใบกำกับเฉพาะออเดอร์ที่ชำระเงินสำเร็จ (payment_status = '1')
$sql = "SELECT o.order_id, o.created_at,
               u.user_firstname, u.user_lastname,
               a.addr_name, a.addr_tax_id
        FROM tbl_orders o
        LEFT JOIN tbl_user u ON o.user_id = u.user_id
        LEFT JOIN tbl_user_address a
               ON a.addr_id = (SELECT addr_id FROM tbl_user_address
                                WHERE addr_user_id = o.user_id AND delete_at IS NULL
                                ORDER BY addr_is_default DESC, addr_id DESC LIMIT 1)
        WHERE o.payment_status = '1'
        ORDER BY o.created_at DESC, o.order_id DESC";
$stmt = $pdo_connect->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

$list_data = [];
foreach ($rows as $r) {
    $ts   = $r['created_at'] ? strtotime($r['created_at']) : time();
    $name = trim((string) ($r['addr_name'] ?? ''));
    if ($name === '') {
        $name = trim(($r['user_firstname'] ?? '') . ' ' . ($r['user_lastname'] ?? ''));
    }
    // ออกสำเร็จ = ลูกค้ามีที่อยู่ + เลขผู้เสียภาษีครบ, ไม่ครบ = ออกไม่สำเร็จ
    $tax = trim((string) ($r['addr_tax_id'] ?? ''));
    $list_data[] = [
        'order_id' => (int) $r['order_id'],
        'doc_no'   => 'ET' . date('ym', $ts) . str_pad((string) $r['order_id'], 7, '0', STR_PAD_LEFT),
        'name'     => $name !== '' ? $name : '-',
        'tax_id'   => $tax !== '' ? $tax : '-',
        'date'     => date('d/m/', $ts) . (date('Y', $ts) + 543),
        'status'   => $tax !== '' ? '1' : '0', // 1=ออกสำเร็จ, 0=ออกไม่สำเร็จ
    ];
}

Response::json(1, 'Success', ['list_data' => $list_data]);
