<?php
// คอร์สเรียนคงเหลือในระบบ — view fragment: render ตาราง + pagination จากข้อมูลที่ส่งมาทาง POST
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
?>
<?php if (!empty($list)): ?>
    <div class="default-table-area">
        <div class="table-responsive">
            <table class="table align-middle w-100" id="PageTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width:60px;">ลำดับ</th>
                        <th>ชื่อสมาชิก</th>
                        <th>เบอร์โทรติดต่อ</th>
                        <th>ชื่อคอร์ส</th>
                        <th>วันที่ซื้อ</th>
                        <th>วันที่เปิดใช้</th>
                        <th>วันหมดอายุ</th>
                        <th class="text-center">อายุคงเหลือ (วัน)</th>
                        <th class="text-end">ราคา</th>
                        <th class="text-center" style="width:90px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = $from; foreach ($list as $row):
                        $enroll_id   = (int) ($row['enroll_id'] ?? 0);
                        $remain_days = $row['remain_days'] ?? null;
                        $expiry      = (string) ($row['expiry'] ?? '');
                    ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td class="fw-medium"><?= $esc(($row['member'] ?? '') !== '' ? $row['member'] : '-') ?></td>
                            <td><?= $esc(($row['phone'] ?? '') !== '' ? $row['phone'] : '-') ?></td>
                            <td><?= $esc(($row['course'] ?? '') !== '' ? $row['course'] : '-') ?></td>
                            <td><?= $esc(($row['buy_at'] ?? '') !== '' ? $row['buy_at'] : '-') ?></td>
                            <td><?= $esc(($row['open_at'] ?? '') !== '' ? $row['open_at'] : '-') ?></td>
                            <td>
                                <?php if ($expiry === ''): ?>
                                    <span class="text-muted">ไม่มีกำหนด</span>
                                <?php else: ?>
                                    <?= $esc($expiry) ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($remain_days === null || $remain_days === ''): ?>
                                    <span class="text-muted">-</span>
                                <?php elseif ((int) $remain_days > 0): ?>
                                    <span class="text-success"><?= (int) $remain_days ?> วัน</span>
                                <?php else: ?>
                                    <span class="text-danger">หมดอายุ</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end"><?= $esc($row['price'] ?? '0.00') ?> ฿</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-info text-white" onclick="OpenEdit(<?= $enroll_id ?>)">แก้ไข</button>
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
