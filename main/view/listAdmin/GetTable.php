<?php

    require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    $list_data = $data["list_data"] ?? [];
    $current_admin_id = isset($data["current_admin_id"]) ? (int) $data["current_admin_id"] : 0;
?>

<div class="card bg-white border-0 rounded-3 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center p-4">
        <h2 class="mb-0">ผู้ดูแลระบบทั้งหมด</h2>
        <a href="admin_fromadd.php" class="btn btn-success">เพิ่มผู้ดูแลระบบใหม่</a>
    </div>

    <div class="card-body p-4">
        <div class="default-table-area">
            <div class="table-responsive">
                <table class="table align-middle w-100" id="PageTable">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center" style="width: 60px;">#</th>
                            <th scope="col">ชื่อ-นามสกุล</th>
                            <th scope="col">อีเมล</th>
                            <th scope="col" class="text-center" style="width: 160px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($list_data) > 0): ?>
                            <?php $n = 1; ?>
                            <?php foreach ($list_data as $row): ?>
                                <?php $is_self = ((int) ($row['user_id'] ?? 0) === $current_admin_id); ?>
                                <tr>
                                    <td class="text-center"><?php echo $n++; ?></td>
                                    <td class="fw-medium"><?php echo htmlspecialchars($row['full_name'] !== '' ? $row['full_name'] : '-'); ?></td>
                                    <td class="text-secondary"><?php echo htmlspecialchars($row['user_email'] ?? '-'); ?></td>
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-warning"
                                                onclick="GetEditAdmin('<?php echo $row['user_id']; ?>');">
                                                แก้ไข
                                            </button>
                                            <?php if (!$is_self): ?>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="DeleteAdmin('<?php echo $row['user_id']; ?>');">
                                                    ลบ
                                                </button>
                                            <?php endif; ?>
                                        </div>
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
