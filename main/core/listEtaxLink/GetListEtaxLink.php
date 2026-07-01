<?php
// รายการลิ้งค์ออกใบกำกับภาษี (สำหรับหน้า etax_link.php) — render ฝั่ง client เหมือน etax.php

use App\Utility\Auth;
use App\Utility\Response;
use App\Database\Connection;

$access_token = Auth::requireUserToken();
$admin_id = $access_token->user_id ?? null;
if (!$admin_id) {
    Response::json(0, 'Unauthorized', null);
}

$db_instance = new Connection();
$pdo = $db_instance->getPdo();
if (!$pdo) {
    Response::json(0, 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', null);
}

try {
    $sql = "SELECT l.id, l.etax_no, l.customer_name, l.doc_date, l.doc_status, l.link_status,
                   l.public_token, l.grand_total,
                   (SELECT product_name FROM tbl_etax_link_item
                     WHERE link_id = l.id AND delete_at IS NULL
                     ORDER BY list_order ASC, id ASC LIMIT 1) AS first_item,
                   (SELECT COUNT(*) FROM tbl_etax_link_item
                     WHERE link_id = l.id AND delete_at IS NULL) AS item_count
            FROM tbl_etax_link l
            WHERE l.delete_at IS NULL
            ORDER BY l.id DESC";
    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $list_data = [];
    foreach ($rows as $r) {
        $first = trim((string) ($r['first_item'] ?? ''));
        $cnt   = (int) ($r['item_count'] ?? 0);
        $items_label = $first !== '' ? $first : '-';
        if ($cnt > 1) { $items_label .= ' (+' . ($cnt - 1) . ' รายการ)'; }

        $ts = $r['doc_date'] ? strtotime($r['doc_date']) : time();
        $date = date('d/m/', $ts) . (date('Y', $ts) + 543);

        $list_data[] = [
            'id'          => (int) $r['id'],
            'etax_no'     => (string) $r['etax_no'],
            'customer'    => (string) ($r['customer_name'] !== '' ? $r['customer_name'] : '-'),
            'items'       => $items_label,
            'date'        => $date,
            'doc_status'  => (string) ($r['doc_status'] ?? '1'),
            'link_status' => (string) ($r['link_status'] ?? '1'),
            'token'       => (string) $r['public_token'],
        ];
    }

    Response::json(1, 'Success', ['list_data' => $list_data]);
} catch (\Throwable $e) {
    error_log('GetListEtaxLink Error: ' . $e->getMessage());
    Response::json(0, 'เกิดข้อผิดพลาด: ' . $e->getMessage(), null);
} finally {
    $pdo = null;
}
