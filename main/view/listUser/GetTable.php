<?php

    require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    $list_data = $data["list_data"] ?? [];
?>

<div class="card bg-white border-0 rounded-3 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-3 p-4">
        <h4 class="mb-0">ผู้ใช้/ลูกค้าทั้งหมด</h4>
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
                            <th scope="col" class="text-center">สถานะการยืนยัน</th>
                            <th scope="col" class="text-center" style="width: 110px;">จัดการ</th>
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
                                <?php $initial = mb_substr($full_name !== '' ? $full_name : '?', 0, 1, 'UTF-8'); ?>
                                <tr>
                                    <td class="text-center"><?php echo $n++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="flex-shrink-0 d-inline-flex align-items-center justify-content-center rounded-circle text-primary fw-medium"
                                                  style="width:38px;height:38px;background:#eef0ff;">
                                                <?php echo htmlspecialchars($initial); ?>
                                            </span>
                                            <span class="ms-2 fw-medium"><?php echo htmlspecialchars($full_name !== '' ? $full_name : '-'); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-secondary"><?php echo htmlspecialchars($row['user_email'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_phone'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_citizen_id'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($cpd !== '' ? $cpd : '-'); ?></td>
                                    <td><?php echo htmlspecialchars($cpa !== '' ? $cpa : '-'); ?></td>
                                    <td class="text-center">
                                        <!-- ยังไม่มีคอลัมน์สถานะการยืนยันในฐานข้อมูล (รอออกแบบเพิ่ม) -->
                                        <span class="text-muted">-</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center justify-content-center p-0"
                                                style="width:34px;height:34px;"
                                                onclick="GetEditUser('<?php echo $row['user_id']; ?>');" title="ดู/แก้ไข">
                                                <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info text-white d-inline-flex align-items-center justify-content-center p-0"
                                                style="width:34px;height:34px;"
                                                onclick="LoginAsUser('<?php echo $row['user_id']; ?>');" title="ล็อกอินเข้าเว็บไซต์">
                                                <span class="material-symbols-outlined" style="font-size:18px;">login</span>
                                            </button>
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
