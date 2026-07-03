<?php
// รีวิวจากลูกค้า — view fragment: render ตาราง + pagination จากข้อมูลที่ส่งมาทาง POST
// รับ: data (list), total, page, per_page  ->  คืน HTML แปะใน #result_box

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

$list     = isset($_POST['data']) && is_array($_POST['data']) ? $_POST['data'] : [];
$total    = (int) ($_POST['total'] ?? count($list));
$page     = max(1, (int) ($_POST['page'] ?? 1));
$per_page = max(1, (int) ($_POST['per_page'] ?? 10));

$esc = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

// ดาวคะแนน: เต็ม/ว่าง 5 ดวง ตามค่า rating (1-5)
$stars = function ($rating): string {
    $n = max(0, min(5, (int) $rating));
    $html = '<span class="review-stars" aria-hidden="true">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<span class="material-symbols-outlined" style="font-size:16px;">' . ($i <= $n ? 'star' : 'star_outline') . '</span>';
    }
    $html .= '</span>';
    return $html;
};
?>
<?php if (!empty($list)): ?>
    <div class="default-table-area">
        <div class="table-responsive">
            <table class="table align-middle w-100">
                <thead>
                    <tr>
                        <th scope="col" class="text-center" style="width: 60px;">#</th>
                        <th scope="col">ผู้รีวิว</th>
                        <th scope="col" class="text-center" style="width: 140px;">คะแนน</th>
                        <th scope="col">ความคิดเห็น</th>
                        <th scope="col" class="text-center" style="width: 130px;">วันที่รีวิว</th>
                        <th scope="col" class="text-center" style="width: 130px;">สถานะแสดงผล</th>
                        <th scope="col" class="text-center" style="width: 180px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n = ($page - 1) * $per_page; foreach ($list as $row): $n++; ?>
                        <tr>
                            <td class="text-center"><?php echo $n; ?></td>
                            <td>
                                <div class="fw-medium"><?php echo $esc($row['reviewer'] ?? '-'); ?></div>
                                <div class="text-secondary small"><?php echo $esc($row['user_email'] ?? ''); ?></div>
                            </td>
                            <td class="text-center"><?php echo $stars($row['rating'] ?? 0); ?></td>
                            <td class="text-secondary"><?php echo nl2br($esc(mb_strimwidth((string) ($row['comment'] ?? ''), 0, 120, '…'))); ?></td>
                            <td class="text-center text-secondary"><?php echo $esc(date('d/m/Y', strtotime((string) ($row['created_at'] ?? '')))); ?></td>
                            <td class="text-center">
                                <?php if ((string) ($row['is_approved'] ?? '0') === '1'): ?>
                                    <span class="badge bg-success">แสดงผล</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">ซ่อนอยู่</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn btn-warning table-action-btn"
                                        onclick="GetEditReview('<?php echo $esc($row['review_id']); ?>');">
                                        <span class="material-symbols-outlined" aria-hidden="true">edit</span>แก้ไข
                                    </button>
                                    <button type="button" class="btn btn-danger table-action-btn"
                                        onclick="GetDeleteReview('<?php echo $esc($row['review_id']); ?>');">
                                        <span class="material-symbols-outlined" aria-hidden="true">delete</span>ลบ
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
        <div class="list-empty-hint">ยังไม่มีรีวิวจากลูกค้า หรือลองปรับเงื่อนไขการค้นหาใหม่อีกครั้ง</div>
    </div>
<?php endif; ?>
