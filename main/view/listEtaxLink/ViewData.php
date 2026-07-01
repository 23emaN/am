<?php
// ลิ้งค์ออกใบกำกับภาษี (E-Tax) — view fragment: render ตาราง + pagination จากข้อมูลที่ส่งมาทาง POST
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
                        <th scope="col" class="text-center" style="width:60px;">ID</th>
                        <th scope="col">เลขใบกำกับ</th>
                        <th scope="col">ลูกค้า</th>
                        <th scope="col">รายการ</th>
                        <th scope="col" class="text-nowrap">วันที่ในใบกำกับ</th>
                        <th scope="col" class="text-center">สถานะเอกสาร</th>
                        <th scope="col" class="text-center">สถานะลิงค์</th>
                        <th scope="col" class="text-center" style="width:200px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $row):
                        $id          = (int) ($row['id'] ?? 0);
                        $token       = (string) ($row['token'] ?? '');
                        $doc_status  = (string) ($row['doc_status'] ?? '1');
                        $link_status = (string) ($row['link_status'] ?? '1');
                        $link_on     = ($link_status === '1');
                        $sq          = 'btn btn-sm d-inline-flex align-items-center justify-content-center p-0';
                    ?>
                        <tr>
                            <td class="text-center"><?= $id ?></td>
                            <td class="fw-medium"><?= $esc($row['etax_no'] ?? '') ?></td>
                            <td><?= $esc($row['customer'] ?? '') ?></td>
                            <td class="text-secondary"><?= $esc($row['items'] ?? '') ?></td>
                            <td class="text-nowrap"><?= $esc($row['date'] ?? '') ?></td>
                            <td class="text-center">
                                <?php if ($doc_status === '2'): ?>
                                    <span class="badge bg-danger">ยกเลิก</span>
                                <?php else: ?>
                                    <span class="badge bg-success">ออกใบกำกับภาษีแล้ว</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($link_on): ?>
                                    <span class="badge bg-success">ใช้งานได้</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">ปิดใช้งาน</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="etax_link_view.php?id=<?= $id ?>" class="<?= $sq ?> btn-info text-white" style="width:34px;height:34px;" title="ดูข้อมูล">
                                        <span class="material-symbols-outlined" style="font-size:18px;">visibility</span></a>
                                    <button type="button" class="<?= $sq ?> btn-secondary" style="width:34px;height:34px;" onclick="CopyLink('<?= $esc($token) ?>')" title="คัดลอกลิ้งค์">
                                        <span class="material-symbols-outlined" style="font-size:18px;">link</span></button>
                                    <button type="button" class="<?= $sq ?> btn-success" style="width:34px;height:34px;" onclick="DownloadEtaxLink(<?= $id ?>)" title="ดาวน์โหลด PDF">
                                        <span class="material-symbols-outlined" style="font-size:18px;">download</span></button>
                                    <button type="button" class="<?= $sq . ($link_on ? ' btn-warning' : ' btn-outline-secondary') ?>" style="width:34px;height:34px;" onclick="ToggleLink(<?= $id ?>)" title="<?= $link_on ? 'ปิดใช้งานลิ้งค์' : 'เปิดใช้งานลิ้งค์' ?>">
                                        <span class="material-symbols-outlined" style="font-size:18px;"><?= $link_on ? 'link_off' : 'link' ?></span></button>
                                    <button type="button" class="<?= $sq ?> btn-danger" style="width:34px;height:34px;" onclick="DeleteLink(<?= $id ?>)" title="ลบ">
                                        <span class="material-symbols-outlined" style="font-size:18px;">delete</span></button>
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
    <div class="text-center py-5 text-muted">
        <span class="material-symbols-outlined" style="font-size:48px;opacity:.4;">inbox</span>
        <div class="mt-2 fw-semibold">ไม่พบข้อมูล</div>
        <div style="font-size:13px;">ลองปรับเงื่อนไขการค้นหาใหม่อีกครั้ง</div>
    </div>
<?php endif; ?>
