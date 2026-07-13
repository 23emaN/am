<?php
// ผู้ใช้/ลูกค้าทั้งหมด — view fragment: render ตาราง + pagination จากข้อมูลที่ส่งมาทาง POST
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
                        <th scope="col" class="text-center">สถานะการยืนยัน</th>
                        <th scope="col" class="text-center">สถานะการใช้งาน</th>

                        <th scope="col" class="text-center" style="width: 110px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n = $from; foreach ($list as $row):
                        $full_name = trim(($row['user_firstname'] ?? '') . ' ' . ($row['user_lastname'] ?? ''));
                        $cpd = trim((string) ($row['user_cpd_no'] ?? ''));
                        $cpa = trim((string) ($row['user_cpa_no'] ?? ''));
                        $initial = mb_substr($full_name !== '' ? $full_name : '?', 0, 1, 'UTF-8');

                        // เช็คว่าบัตรประชาชนหมดอายุหรือยัง (id_card_expiry_date เก็บเป็น Y-m-d)
                        $id_expiry = trim((string) ($row['id_card_expiry_date'] ?? ''));
                        $id_expired = false;
                        if ($id_expiry !== '' && $id_expiry !== '0000-00-00') {
                            $exp_ts = strtotime($id_expiry);
                            if ($exp_ts !== false) {
                                $id_expired = $exp_ts < strtotime(date('Y-m-d'));
                            }
                        }
                    ?>
                        <tr>
                            <td class="text-center"><?php echo $n++; ?></td>
                            <td class="text-nowrap" style="min-width: 200px;">
                                <div class="d-flex align-items-center">
                                    <span class="avatar-initial" aria-hidden="true"><?php echo $esc($initial); ?></span>
                                    <div class="ms-2">
                                        <span class="fw-medium"><?php echo $esc($full_name !== '' ? $full_name : '-'); ?></span>
                                        <?php if ($id_expired): ?>
                                            <div class="text-danger small fw-medium">
                                                <span class="material-symbols-outlined align-middle" style="font-size: 1rem;" aria-hidden="true">warning</span>
                                                บัตรประชาชนหมดอายุ
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="text-secondary text-nowrap"><?php echo $esc(($row['user_email'] ?? '') !== '' ? $row['user_email'] : '-'); ?></td>
                            <td class="text-nowrap"><?php echo $esc(($row['user_phone'] ?? '') !== '' ? $row['user_phone'] : '-'); ?></td>
                            <td class="text-nowrap"><?php echo $esc(($row['user_citizen_id'] ?? '') !== '' ? $row['user_citizen_id'] : '-'); ?></td>
                            <td class="text-nowrap"><?php echo $esc($cpd !== '' ? $cpd : '-'); ?></td>
                            <td class="text-nowrap"><?php echo $esc($cpa !== '' ? $cpa : '-'); ?></td>
                            <td class="text-center">
                                <?php
                                    $iv = (string) ($row['identity_verified'] ?? '0');
                                    if ($iv === '2') {
                                        echo '<span class="badge bg-success">ยืนยันแล้ว</span>';
                                    } elseif ($iv === '1') {
                                        echo '<span class="badge bg-warning text-dark">รอตรวจสอบ</span>';
                                    } else {
                                        echo '<span class="badge bg-secondary">ยังไม่ยืนยัน</span>';
                                    }
                                ?>
                            </td>
                            <td class="text-center">
                                <?php
                                    $u_active = (string) ($row['user_status'] ?? '1') === '1';
                                    $u_next   = $u_active ? '0' : '1';
                                ?>
                                <span class="badge <?php echo $u_active ? 'bg-success' : 'bg-secondary'; ?>"
                                      style="cursor:pointer; user-select:none;"
                                      title="คลิกเพื่อ<?php echo $u_active ? 'ปิด' : 'เปิด'; ?>ใช้งาน"
                                      onclick="ToggleUserStatus('<?php echo $esc($row['user_id']); ?>', '<?php echo $u_next; ?>');">
                                    <?php echo $u_active ? 'ใช้งาน' : 'ไม่ใช้งาน'; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-primary icon-btn"
                                        onclick="GetEditUser('<?php echo $esc($row['user_id']); ?>');" title="ดู/แก้ไข" aria-label="ดู/แก้ไขผู้ใช้">
                                        <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-info text-white icon-btn"
                                        onclick="LoginAsUser('<?php echo $esc($row['user_id']); ?>');" title="ล็อกอินเข้าเว็บไซต์" aria-label="ล็อกอินเข้าเว็บไซต์แทนผู้ใช้">
                                        <span class="material-symbols-outlined" aria-hidden="true">login</span>
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
