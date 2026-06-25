<?php

    require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    $list_data = $data["list_data"] ?? [];

    // ดึงรหัสวิชาตามประเภท (cpd หรือ cpa) เฉพาะที่ไม่ว่าง
    function course_codes(array $row, string $type): array {
        $codes = [];
        foreach (['1', '2', '3', '4'] as $i) {
            $v = trim((string)($row["course_code_{$type}_{$i}"] ?? ''));
            $v = str_replace(['[', ']'], '', $v); // ตัดวงเล็บปี/ไตรมาสออกตอนแสดงผล
            if ($v !== '') { $codes[] = $v; }
        }
        return $codes;
    }
?>

<div class="card bg-white border-0 rounded-3 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center p-4">
        <h2 class="mb-0">คอร์สเรียน</h2>

        <div class="d-flex gap-2">
            <a href="course_type.php" class="btn btn-info">จัดการประเภท</a>
            <a href="course_category.php" class="btn btn-info">จัดการหมวดหมู่</a>
            <a href="course_fromadd.php" class="btn btn-success">เพิ่มคอร์สเรียน</a>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="default-table-area">
            <div class="table-responsive">
                <table class="table align-middle w-100" id="PageTable">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center" style="width: 60px;">#</th>
                            <th scope="col" class="text-center" style="width: 90px;">รูป</th>
                            <th scope="col">ประเภท / หมวดหมู่</th>
                            <th scope="col">ชื่อคอร์สเรียน</th>
                            <th scope="col" class="text-end">ราคา</th>
                            <th scope="col">รหัสวิชา CPD</th>
                            <th scope="col">รหัสวิชา CPA</th>
                            <th scope="col" class="text-center">แสดงในหน้าหลัก</th>
                            <th scope="col" class="text-center">สถานะ</th>
                            <th scope="col" class="text-center" style="width: 110px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($list_data) > 0): ?>
                            <?php $n = 1; ?>
                            <?php foreach ($list_data as $row): ?>
                                <?php
                                    $codes_cpd  = course_codes($row, 'cpd');
                                    $codes_cpa  = course_codes($row, 'cpa');
                                    $price      = (float)($row['course_price'] ?? 0);
                                    $promotion  = (float)($row['course_promotion'] ?? 0);
                                    $cover      = trim((string)($row['course_cover_image'] ?? ''));
                                    $is_display = (string)($row['course_display'] ?? '0') === '1';
                                    $is_active  = (string)($row['course_status'] ?? '0') === '1';
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $n++; ?></td>

                                    <td class="text-center">
                                        <?php if ($cover !== ''): ?>
                                            <img src="../<?php echo htmlspecialchars($cover); ?>" alt="cover"
                                                 style="width:64px;height:48px;object-fit:cover;border-radius:6px;">
                                        <?php else: ?>
                                            <span class="text-muted small"><i class="ri-image-line"></i> ไม่มีรูป</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium"><?php echo htmlspecialchars($row['type_name'] ?? '-'); ?></span>
                                            <span class="text-secondary small"><?php echo htmlspecialchars($row['group_name'] ?? '-'); ?></span>
                                        </div>
                                    </td>

                                    <td class="text-secondary"><?php echo htmlspecialchars($row['course_name'] ?? ''); ?></td>

                                    <td class="text-end">
                                        <?php if ($promotion > 0 && $promotion < $price): ?>
                                            <span class="text-muted text-decoration-line-through small"><?php echo number_format($price, 2); ?></span><br>
                                            <span class="fw-medium text-danger"><?php echo number_format($promotion, 2); ?></span>
                                        <?php else: ?>
                                            <span class="fw-medium"><?php echo number_format($price, 2); ?></span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if (count($codes_cpd) > 0): ?>
                                            <div class="d-flex flex-column align-items-start gap-1">
                                                <?php foreach ($codes_cpd as $code): ?>
                                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($code); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if (count($codes_cpa) > 0): ?>
                                            <div class="d-flex flex-column align-items-start gap-1">
                                                <?php foreach ($codes_cpa as $code): ?>
                                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($code); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if ($is_display): ?>
                                            <span class="badge bg-success">แสดง</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">ไม่แสดง</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if ($is_active): ?>
                                            <span class="badge bg-success">เปิดใช้งาน</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">ปิดใช้งาน</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-warning w-100"
                                            onclick="GetEditCourse('<?php echo $row['course_id']; ?>');">
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
