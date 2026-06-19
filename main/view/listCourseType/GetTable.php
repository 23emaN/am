<?php

    require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    $list_data = $data["list_data"] ?? [];
?>

<div class="card bg-white border-0 rounded-3 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center p-4">
        <h2 class="mb-0">ประเภทคอร์สเรียน</h2>

        <div class="d-flex gap-2">
            <a href="course.php" class="btn btn-outline-secondary">กลับไปคอร์สเรียน</a>
            <button class="btn btn-success" type="button" onclick="GetModalAdd()">เพิ่มประเภทใหม่</button>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="default-table-area">
            <div class="table-responsive">
                <table class="table align-middle w-100" id="PageTable">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center" style="width: 80px;">#</th>
                            <th scope="col" class="text-start">ชื่อประเภท</th>
                            <th scope="col" class="text-end" style="width: 180px;">จำนวนคอร์สเรียน</th>
                            <th scope="col" class="text-center" style="width: 120px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($list_data) > 0): ?>
                            <?php $n = 1; ?>
                            <?php foreach ($list_data as $row): ?>
                                <tr>
                                    <td class="text-center"><?php echo $n++; ?></td>
                                    <td class="text-secondary"><?php echo htmlspecialchars($row["type_name"] ?? ""); ?></td>
                                    <td class="text-end text-secondary"><?php echo htmlspecialchars($row["course_count"] ?? 0); ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-warning w-100 mb-1"
                                            onclick="GetEditType('<?php echo $row['type_id']; ?>');">
                                            แก้ไข
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger w-100 mb-1"
                                            onclick="GetDeleteType('<?php echo $row['type_id']; ?>');">
                                            ลบ
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
