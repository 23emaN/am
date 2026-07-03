<?php
// คำขอยืนยันตัวตนผู้ใช้งาน — view fragment: render ตาราง + pagination จากข้อมูลที่ส่งมาทาง POST
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
                        <th scope="col" class="text-center" style="width: 60px;">#</th>
                        <th scope="col">ชื่อ</th>
                        <th scope="col">อีเมล</th>
                        <th scope="col">เบอร์โทรศัพท์</th>
                        <th scope="col">เลขบัตรประชาชน</th>
                        <th scope="col">เลขที่ผู้ทำบัญชี</th>
                        <th scope="col">เลขที่ผู้สอบบัญชี</th>
                        <th scope="col" class="text-center" style="width: 140px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = $from; foreach ($list as $row):
                        $full_name = trim(($row['user_firstname'] ?? '') . ' ' . ($row['user_lastname'] ?? ''));
                        $cpd = trim((string) ($row['user_cpd_no'] ?? ''));
                        $cpa = trim((string) ($row['user_cpa_no'] ?? ''));
                        $user_id = (int) ($row['user_id'] ?? 0);
                    ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td class="fw-medium"><?= $esc($full_name !== '' ? $full_name : '-') ?></td>
                            <td class="text-secondary"><?= $esc($row['user_email'] ?? '-') ?></td>
                            <td><?= $esc($row['user_phone'] ?? '-') ?></td>
                            <td><?= $esc($row['user_citizen_id'] ?? '-') ?></td>
                            <td><?= $esc($cpd !== '' ? $cpd : 'ไม่มีข้อมูล') ?></td>
                            <td><?= $esc($cpa !== '' ? $cpa : 'ไม่มีข้อมูล') ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-warning"
                                    onclick="OpenVerify('<?= $user_id ?>');">
                                    ตรวจเอกสาร
                                </button>
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
