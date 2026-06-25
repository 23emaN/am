<?php

    require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    $list_data = $data["list_data"] ?? [];

?>

<div class="card bg-white border-0 rounded-3 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center p-4">
        <h2 class="mb-0">แบนเนอร์</h2>
        <a href="banner_fromadd.php" class="btn btn-success">สร้างแบนเนอร์ใหม่</a>
    </div>

    <div class="card-body p-4">
        <div class="default-table-area">
            <div class="table-responsive">
                <table class="table align-middle w-100" id="PageTable">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center" style="width: 70px;">ลำดับ</th>
                            <th scope="col">ตัวอย่าง</th>
                            <th scope="col">ลิงก์ปลายทาง</th>
                            <th scope="col" class="text-center">สถานะ</th>
                            <th scope="col" class="text-center" style="width: 130px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($list_data) > 0): ?>
                            <?php foreach ($list_data as $row): ?>
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
                                                 style="max-height: 100px; max-width: 300px; object-fit: cover; border-radius: 6px;"
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
                                        <button type="button" class="btn btn-sm btn-info text-white w-100"
                                            onclick="GetEditBanner('<?php echo $row['banner_id']; ?>');">
                                            แก้ไข
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
