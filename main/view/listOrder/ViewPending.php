<?php
// คำสั่งซื้อรอยืนยัน — view fragment: render ตาราง + pagination จากข้อมูลที่ส่งมาทาง POST
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

// วันที่แสดง 2 บรรทัด: บรรทัดบน = วันที่, บรรทัดล่าง = เวลา (เล็ก/จาง)
$date2 = function ($v) use ($esc): string {
    $v = trim((string) $v);
    if ($v === '') { return '<span class="text-muted">-</span>'; }
    $p = preg_split('/\s+/', $v, 2);
    $out = $esc($p[0]);
    if (isset($p[1]) && $p[1] !== '') { $out .= '<br><span class="text-secondary small">' . $esc($p[1]) . '</span>'; }
    return $out;
};
?>
<?php if (!empty($list)): ?>
    <div class="default-table-area">
        <div class="table-responsive">
            <table class="table align-middle w-100" id="PageTable">
                <thead>
                    <tr>
                        <th scope="col" class="text-center" style="width:60px;">ลำดับ</th>
                        <th scope="col" style="min-width:160px;">ชื่อลูกค้า</th>
                        <th scope="col" style="min-width:280px;">คอร์สเรียน</th>
                        <th scope="col" class="text-end text-nowrap" style="width:1%;">ยอดรวม</th>
                        <th scope="col" class="text-nowrap" style="width:1%;">สั่งซื้อเมื่อ</th>
                        <th scope="col" class="text-center text-nowrap" style="width:1%;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = $from; foreach ($list as $row):
                        $order_id = (int) ($row['order_id'] ?? 0);
                        $customer = ($row['customer'] ?? '') !== '' ? $row['customer'] : '-';
                        $courses  = trim((string) ($row['courses'] ?? ''));
                        $course_html = $courses !== ''
                            ? implode('<br>', array_map($esc, explode("\n", $courses)))
                            : '<span class="text-muted">-</span>';
                        $created = $date2($row['created'] ?? '');
                    ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td class="fw-medium"><?= $esc($customer) ?></td>
                            <td class="text-secondary"><?= $course_html ?></td>
                            <td class="text-end text-nowrap"><?= $esc($row['total'] ?? '') ?></td>
                            <td class="text-nowrap"><?= $created ?></td>
                            <td class="text-center text-nowrap">
                                <a href="order_detail.php?id=<?= $order_id ?>" class="btn btn-sm btn-info text-white">ดูรายละเอียด</a>
                                <button type="button" class="btn btn-sm btn-danger" onclick="CancelOrderRow(<?= $order_id ?>)">ยกเลิกคำสั่งซื้อ</button>
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
