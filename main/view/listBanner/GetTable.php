<?php
// แบนเนอร์ทั้งหมด — view fragment: render ตาราง + pagination จากข้อมูลที่ส่งมาทาง POST
// รับ: data (list), total, page, per_page  ->  คืน HTML แปะใน #result_box

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

$list     = isset($_POST['data']) && is_array($_POST['data']) ? $_POST['data'] : [];
$total    = (int) ($_POST['total'] ?? count($list));
$page     = max(1, (int) ($_POST['page'] ?? 1));
$per_page = max(1, (int) ($_POST['per_page'] ?? 10));
$total_pages = (int) ceil($total / $per_page);
$from = $total > 0 ? ($page - 1) * $per_page + 1 : 0;
$to   = min($page * $per_page, $total);
?>
<?php if (!empty($list)): ?>
    <div class="default-table-area">
        <div class="table-responsive">
            <table class="table align-middle w-100" id="PageTable">
                <thead>
                    <tr>
                        <th scope="col" class="text-center" style="width: 70px;">ลำดับ</th>
                        <th scope="col">ตัวอย่าง</th>
                        <th scope="col">ลิงก์ปลายทาง</th>
                        <th scope="col" class="text-center">สถานะ</th>
                        <th scope="col" class="text-center" style="width: 110px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $row): ?>
                        <?php
                            $is_active = (string)($row['banner_status'] ?? '0') === '1';
                            $img_path  = !empty($row['banner_image']) ? '../' . htmlspecialchars($row['banner_image']) : '';
                            $url       = !empty($row['banner_url']) ? htmlspecialchars($row['banner_url']) : '-';
                        ?>
                        <tr>
                            <td class="text-center fw-bold"><?php echo (int)$row['banner_order']; ?></td>
                            <td>
                                <?php if ($img_path): ?>
                                    <img src="<?php echo $img_path; ?>"
                                         alt="banner"
                                         style="max-height: 100px; max-width: 300px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border);"
                                         onerror="this.style.display='none'">
                                <?php else: ?>
                                    <span class="text-muted small">ไม่มีรูป</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($url !== '-'): ?>
                                    <a href="<?php echo $url; ?>" target="_blank" class="text-primary text-break small">
                                        <?php echo $url; ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($is_active): ?>
                                    <span class="badge bg-success">เปิดใช้งาน</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">ไม่ได้เปิดใช้งาน</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    <button type="button" class="btn btn-sm btn-info text-white"
                                        onclick="GetEditBanner('<?php echo $row['banner_id']; ?>');">
                                        แก้ไข
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
