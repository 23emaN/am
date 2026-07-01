<?php
// ใบรับรองผลการสอบ — view fragment: render ตาราง + pagination จากข้อมูลที่ส่งมาทาง POST
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
            <table class="table align-middle w-100" id="CertTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width:60px;">#</th>
                        <th class="text-nowrap">เลขที่ใบรับรอง</th>
                        <th>คอร์สเรียน</th>
                        <th class="text-nowrap">ผู้สอบ</th>
                        <th class="text-nowrap">คะแนนที่ได้</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">การอนุมัติ</th>
                        <th class="text-center" style="width:160px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = $from; foreach ($list as $row):
                        $passed    = ((string) ($row['passed']   ?? '0') === '1');
                        $approved  = ((string) ($row['approved'] ?? '0') === '1');
                        $enroll_id = (int) ($row['enroll_id'] ?? 0);
                        $score     = $row['score'] ?? '';
                        $score_txt = ($score === '' || $score === null)
                            ? '<span class="text-muted">-</span>'
                            : $esc($score) . ' คะแนน / ' . $esc($row['percent'] ?? '0.00') . ' %';
                    ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td class="text-nowrap"><?= $esc($row['cert_no'] ?? '') ?></td>
                            <td><?= $esc(($row['course'] ?? '') !== '' ? $row['course'] : '-') ?></td>
                            <td class="text-nowrap"><?= $esc(($row['examiner'] ?? '') !== '' ? $row['examiner'] : '-') ?></td>
                            <td class="text-nowrap"><?= $score_txt ?></td>
                            <td class="text-center">
                                <?php if ($passed): ?>
                                    <span class="badge bg-success">ผ่าน</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">ไม่ผ่าน</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($passed): ?>
                                    <?php if ($approved): ?>
                                        <span class="badge bg-success">อนุมัติ</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">รออนุมัติ</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center text-nowrap">
                                <?php if ($passed && $approved): ?>
                                    <button type="button" class="btn btn-sm btn-info text-white me-1" onclick="DownloadCert(<?= $enroll_id ?>)">ดาวน์โหลด</button>
                                <?php endif; ?>
                                <?php if ($passed): ?>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="OpenManage(<?= $enroll_id ?>)">ดำเนินการ</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include dirname(__DIR__) . '/_pagination.php'; ?>
<?php else: ?>
    <div class="text-center py-5 text-muted">
        <span class="material-symbols-outlined" style="font-size:48px;opacity:.4;">inbox</span>
        <div class="mt-2 fw-semibold">ไม่พบข้อมูล</div>
        <div style="font-size:13px;">ลองปรับเงื่อนไขการค้นหาใหม่อีกครั้ง</div>
    </div>
<?php endif; ?>
