<?php
// ประเภทคอร์สเรียน — view fragment: render ตาราง + pagination จากข้อมูลที่ส่งมาทาง POST
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
            <table class="table align-middle w-100" id="PageTable" style="table-layout: fixed;">
                <thead>
                    <tr>
                        <th scope="col" class="text-center" style="width: 8%;">#</th>
                        <th scope="col" class="text-start" style="width: 52%;">ชื่อประเภท</th>
                        <th scope="col" class="text-center" style="width: 18%;">จำนวนคอร์สเรียน</th>
                        <th scope="col" class="text-center" style="width: 22%;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = $from; foreach ($list as $row): ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td class="text-secondary"><?= $esc($row["type_name"] ?? "") ?></td>
                            <td class="text-secondary text-center"><?= $esc($row["course_count"] ?? 0) ?></td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-warning"
                                        onclick="GetEditType('<?= $esc($row['type_id']) ?>');">
                                        แก้ไข
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger"
                                        onclick="GetDeleteType('<?= $esc($row['type_id']) ?>');">
                                        ลบ
                                    </button>
                                </div>
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
