<?php
// รายละเอียดคำสั่งซื้อ 1 รายการ: ข้อมูลทั่วไป + ใบกำกับภาษี + รายการ + สรุป VAT

use App\Utility\Auth;
use App\Utility\Response;
use App\Database\Connection;

$access_token = Auth::requireUserToken();
$admin_id = $access_token->user_id ?? null;
if (!$admin_id) {
    Response::json(0, 'Unauthorized', null);
}

$order_id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
if ($order_id <= 0) {
    Response::json(0, 'ไม่พบรหัสคำสั่งซื้อ', null);
}

$db_instance = new Connection();
$pdo_connect = $db_instance->getPdo();
if (!$pdo_connect) {
    Response::json(0, 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', null);
}

// order + ลูกค้า
$stmt = $pdo_connect->prepare(
    "SELECT o.order_id, o.user_id, o.transaction_ref, o.total_price, o.payment_status, o.payment_method, o.created_at,
            o.order_internal_note,
            u.user_firstname, u.user_lastname, u.user_phone, u.user_email
     FROM tbl_orders o
     LEFT JOIN tbl_user u ON o.user_id = u.user_id
     WHERE o.order_id = :id
     LIMIT 1"
);
$stmt->execute([':id' => $order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();
if (!$order) {
    Response::json(0, 'ไม่พบคำสั่งซื้อนี้', null);
}

// รายการคอร์สในคำสั่งซื้อ
$it = $pdo_connect->prepare(
    "SELECT od.price_at_purchase, c.course_name
     FROM tbl_order_detail od
     LEFT JOIN tbl_course c ON c.course_id = od.course_id
     WHERE od.order_id = :id
     ORDER BY od.list_order ASC, od.detail_id ASC"
);
$it->execute([':id' => $order_id]);
$items = $it->fetchAll(PDO::FETCH_ASSOC);
$it->closeCursor();

// ที่อยู่ใบกำกับภาษี (ค่าเริ่มต้นของลูกค้า)
$ad = $pdo_connect->prepare(
    "SELECT addr_id, addr_type, addr_name, addr_tax_id, addr_branch, addr_phone, addr_detail,
            addr_subdistrict, addr_district, addr_province, addr_zipcode
     FROM tbl_user_address
     WHERE addr_user_id = :uid AND delete_at IS NULL
     ORDER BY addr_is_default DESC, addr_id DESC
     LIMIT 1"
);
$ad->execute([':uid' => (int) $order['user_id']]);
$addr = $ad->fetch(PDO::FETCH_ASSOC) ?: null;
$ad->closeCursor();

$full = trim(($order['user_firstname'] ?? '') . ' ' . ($order['user_lastname'] ?? ''));

$type_label = fn($t) => $t === '2' ? 'นิติบุคคล' : 'บุคคลธรรมดา';

// ใบกำกับภาษี (ค่าแสดงผล)
$receipt = [
    'type'    => '-',
    'tax_id'  => '-',
    'branch'  => '-',
    'name'    => $full !== '' ? $full : '-',
    'address' => '-',
    'phone'   => $order['user_phone'] ?: '-',
];
// ค่าดิบสำหรับ prefill โมดัลแก้ไขที่อยู่
$receipt_raw = [
    'addr_id'     => $addr['addr_id'] ?? 0,
    'type'        => $addr['addr_type'] ?? '1',
    'email'       => $order['user_email'] ?? '',
    'name'        => $addr['addr_name'] ?? '',
    'tax_id'      => $addr['addr_tax_id'] ?? '',
    'branch'      => $addr['addr_branch'] ?? '',
    'phone'       => $addr['addr_phone'] ?? '',
    'detail'      => $addr['addr_detail'] ?? '',
    'subdistrict' => $addr['addr_subdistrict'] ?? '',
    'district'    => $addr['addr_district'] ?? '',
    'province'    => $addr['addr_province'] ?? '',
    'zipcode'     => $addr['addr_zipcode'] ?? '',
];
if ($addr) {
    $addr_text = trim(implode(' ', array_filter([
        $addr['addr_detail'] ?? '',
        $addr['addr_subdistrict'] ?? '',
        $addr['addr_district'] ?? '',
        $addr['addr_province'] ?? '',
        $addr['addr_zipcode'] ?? '',
    ])));
    $receipt = [
        'type'    => $type_label((string) ($addr['addr_type'] ?? '1')),
        'tax_id'  => $addr['addr_tax_id'] ?: '-',
        'branch'  => $addr['addr_branch'] ?: '-',
        'name'    => $addr['addr_name'] ?: ($full !== '' ? $full : '-'),
        'address' => $addr_text !== '' ? $addr_text : '-',
        'phone'   => $addr['addr_phone'] ?: ($order['user_phone'] ?: '-'),
    ];
}

// สรุป VAT (ราคารวมภาษีมูลค่าเพิ่ม 7%)
$total  = (float) ($order['total_price'] ?? 0);
$before = round($total / 1.07, 2);
$vat    = round($total - $before, 2);

Response::json(1, 'Success', [
    'order' => [
        'order_id'       => (int) $order['order_id'],
        'ref'            => $order['transaction_ref'] ?? '',
        'customer'       => $full !== '' ? $full : '-',
        'total'          => $total,
        'payment_status' => (string) ($order['payment_status'] ?? '0'),
        'payment_method' => (string) ($order['payment_method'] ?? ''),
        'created_at'     => $order['created_at'] ? date('d/m/Y H:i:s', strtotime($order['created_at'])) : '-',
        'user_id'        => (int) $order['user_id'],
        'internal_note'  => $order['order_internal_note'] ?? '',
    ],
    'receipt'     => $receipt,
    'receipt_raw' => $receipt_raw,
    'items'   => array_map(fn($x) => [
        'course_name' => $x['course_name'] ?? '-',
        'price'       => (float) ($x['price_at_purchase'] ?? 0),
    ], $items),
    'summary' => ['before' => $before, 'vat' => $vat, 'total' => $total],
]);
