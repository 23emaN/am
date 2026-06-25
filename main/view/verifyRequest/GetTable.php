<?php

    require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    $list_data = $data["list_data"] ?? [];
?>

<div class="card bg-white border-0 rounded-3 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center p-4">
        <h2 class="mb-0">คำขอยืนยันตัวตนผู้ใช้งาน</h2>
    </div>

    <div class="card-body p-4">
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
                        <?php if (count($list_data) > 0): ?>
                            <?php $n = 1; ?>
                            <?php foreach ($list_data as $row): ?>
                                <?php
                                    $full_name = trim(($row['user_firstname'] ?? '') . ' ' . ($row['user_lastname'] ?? ''));
                                    $cpd = trim((string)($row['user_cpd_no'] ?? ''));
                                    $cpa = trim((string)($row['user_cpa_no'] ?? ''));
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $n++; ?></td>
                                    <td class="fw-medium"><?php echo htmlspecialchars($full_name !== '' ? $full_name : '-'); ?></td>
                                    <td class="text-secondary"><?php echo htmlspecialchars($row['user_email'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_phone'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_citizen_id'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($cpd !== '' ? $cpd : 'ไม่มีข้อมูล'); ?></td>
                                    <td><?php echo htmlspecialchars($cpa !== '' ? $cpa : 'ไม่มีข้อมูล'); ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-warning"
                                            onclick="OpenVerify('<?php echo $row['user_id']; ?>');">
                                            ตรวจเอกสาร
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
