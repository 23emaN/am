<?php
// คำสั่งซื้อทั้งหมด — view fragment: render ตาราง + pagination จากข้อมูลที่ส่งมาทาง POST
// รับ: data (list), total, page, per_page  ->  คืน HTML แปะใน #result_box

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

$list     = isset($_POST['data']) && is_array($_POST['data']) ? $_POST['data'] : [];
$total    = (int) ($_POST['total'] ?? count($list));
$page     = max(1, (int) ($_POST['page'] ?? 1));
$per_page = max(1, (int) ($_POST['per_page'] ?? 10));
$total_pages = (int) ceil($total / $per_page);
$from = $total > 0 ? ($page - 1) * $per_page + 1 : 0;
$to   = min($page * $per_page, $total);

$esc = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

// แปลงสถานะ -> badge (payment_status: 0=รอชำระ 1=สำเร็จ 2=ยกเลิก)
$status_badge = function (string $s): string {
    if ($s === '1') { return '<span class="badge bg-success">สำเร็จแล้ว</span>'; }
    if ($s === '2') { return '<span class="badge bg-danger">ยกเลิก</span>'; }
    return '<span class="badge bg-secondary">รอชำระเงิน</span>';
};
$payment_badge = function (string $s): string {
    if ($s === '1') { return '<span class="badge bg-success">ชำระแล้ว</span>'; }
    if ($s === '2') { return '<span class="badge bg-secondary">ยกเลิก</span>'; }
    return '<span class="badge bg-danger">ยังไม่ได้ชำระ</span>';
};
?>
<?php if (!empty($list)): ?>
    <div class="default-table-area">
        <div class="table-responsive">
            <table class="table align-middle w-100" id="PageTable">
                <thead>
                    <tr>
                        <th scope="col" class="text-center" style="width:60px;">ลำดับ</th>
                        <th scope="col">หมายเลขคำสั่งซื้อ</th>
                        <th scope="col">ชื่อลูกค้า</th>
                        <th scope="col">คอร์สเรียน</th>
                        <th scope="col" class="text-end">ยอดรวม</th>
                        <th scope="col" class="text-center">สถานะ</th>
                        <th scope="col" class="text-center">สถานะการชำระเงิน</th>
                        <th scope="col">สั่งซื้อเมื่อ</th>
                        <th scope="col" class="text-center">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = $from; foreach ($list as $row):
                        $order_id = (int) ($row['order_id'] ?? 0);
                        $status   = (string) ($row['status'] ?? '0');
                        $ref      = trim((string) ($row['order_no'] ?? ''));
                        $full     = trim((string) ($row['customer'] ?? ''));
                        $courses  = isset($row['courses']) && is_array($row['courses']) ? $row['courses'] : [];
                        $course_html = !empty($courses)
                            ? implode('<br>', array_map($esc, $courses))
                            : '<span class="text-muted">-</span>';
                    ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td><?= $ref !== '' ? $esc($ref) : '<span class="text-muted">ไม่มีข้อมูล</span>' ?></td>
                            <td class="fw-medium"><?= $esc($full !== '' ? $full : '-') ?></td>
                            <td class="text-secondary"><?= $course_html ?></td>
                            <td class="text-end"><?= number_format((float) ($row['total'] ?? 0), 2) ?> บาท</td>
                            <td class="text-center"><?= $status_badge($status) ?></td>
                            <td class="text-center"><?= $payment_badge($status) ?></td>
                            <td><?= ($row['created'] ?? '') !== '' ? $esc($row['created']) : '-' ?></td>
                            <td class="text-center">
                                <a href="order_detail.php?id=<?= $order_id ?>" class="btn btn-sm btn-info text-white">ดูรายละเอียด</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include dirname(__DIR__) . '/_pagination.php'; ?>
<?php else: ?>
    <div class="list-empty">
        <div class="list-empty-icon"><span class="material-symbols-outlined" aria-hidden="true">inbox</span></div>
        <div class="list-empty-title">ไม่พบข้อมูล</div>
        <div class="list-empty-hint">ลองปรับเงื่อนไขการค้นหาใหม่อีกครั้ง</div>
    </div>
<?php endif; ?>
