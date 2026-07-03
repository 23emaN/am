<?php
// รายการใบกำกับภาษี (E-Tax) — 1 ออเดอร์ที่ชำระแล้ว = 1 ใบกำกับภาษี
// ข้อมูลลูกค้า/ที่อยู่จาก tbl_user_address (ที่อยู่ default ของลูกค้า)
// เลขเอกสาร/สถานะ = ระบบเราออกเอง
// แบ่งหน้าฝั่ง server (LIMIT/OFFSET) + ค้นหา -> คืน JSON { list, total, page, per_page }
// หน้า etax นำไป render ผ่าน view/listEtax/ViewData.php

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

$search = trim((string) ($_POST['search'] ?? ''));   // เลขที่เอกสาร / ชื่อลูกค้า / เลขผู้เสียภาษี

// ที่อยู่ default ของลูกค้า (ใช้ทั้งใน SELECT และเงื่อนไขค้นหา)
$addr_join = "LEFT JOIN tbl_user_address a
                     ON a.addr_id = (SELECT addr_id FROM tbl_user_address
                                      WHERE addr_user_id = o.user_id AND delete_at IS NULL
                                      ORDER BY addr_is_default DESC, addr_id DESC LIMIT 1)";

$joins = "FROM tbl_orders o
          LEFT JOIN tbl_user u ON o.user_id = u.user_id
          $addr_join";

// ออกใบกำกับเฉพาะออเดอร์ที่ชำระเงินสำเร็จ (payment_status = '1')
$where  = ["o.payment_status = '1'"];
$params = [];
if ($search !== '') {
    // ชื่อ = addr_name หรือ ชื่อ-นามสกุลผู้ใช้ ; เลขผู้เสียภาษี = addr_tax_id ; เลขที่เอกสาร = order_id
    $where[] = "(COALESCE(a.addr_name, '') LIKE :search
                 OR CONCAT_WS(' ', u.user_firstname, u.user_lastname) LIKE :search
                 OR COALESCE(a.addr_tax_id, '') LIKE :search
                 OR CAST(o.order_id AS CHAR) LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
$where_sql = 'WHERE ' . implode(' AND ', $where);

try {
    // จำนวนทั้งหมดหลังกรอง
    $stmt_cnt = $pdo_connect->prepare("SELECT COUNT(*) $joins $where_sql");
    $stmt_cnt->execute($params);
    $total = (int) $stmt_cnt->fetchColumn();
    $stmt_cnt->closeCursor();

    // ข้อมูลหน้าปัจจุบัน (ใหม่สุดก่อน)
    $sql = "SELECT o.order_id, o.created_at,
                   u.user_firstname, u.user_lastname,
                   a.addr_name, a.addr_tax_id
            $joins
            $where_sql
            ORDER BY o.created_at DESC, o.order_id DESC
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
        $ts   = $r['created_at'] ? strtotime($r['created_at']) : time();
        $name = trim((string) ($r['addr_name'] ?? ''));
        if ($name === '') {
            $name = trim(($r['user_firstname'] ?? '') . ' ' . ($r['user_lastname'] ?? ''));
        }
        // ออกสำเร็จ = ลูกค้ามีที่อยู่ + เลขผู้เสียภาษีครบ, ไม่ครบ = ออกไม่สำเร็จ
        $tax = trim((string) ($r['addr_tax_id'] ?? ''));
        $list[] = [
            'order_id' => (int) $r['order_id'],
            'doc_no'   => 'ET' . date('ym', $ts) . str_pad((string) $r['order_id'], 7, '0', STR_PAD_LEFT),
            'name'     => $name !== '' ? $name : '-',
            'tax_id'   => $tax !== '' ? $tax : '-',
            'date'     => date('d/m/', $ts) . (date('Y', $ts) + 543),
            'status'   => $tax !== '' ? '1' : '0', // 1=ออกสำเร็จ, 0=ออกไม่สำเร็จ
        ];
    }

    Response::json(1, 'สำเร็จ', ['list' => $list, 'total' => $total, 'page' => $page, 'per_page' => $per_page]);

} catch (\Throwable $e) {
    error_log('GetListEtax Error: ' . $e->getMessage());
    Response::json(0, 'เกิดข้อผิดพลาด: ' . $e->getMessage(), null);
}
