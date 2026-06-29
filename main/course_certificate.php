<?php
    $breadcrumbs = [['label' => 'ใบรับรองผลการสอบ']];
    require_once dirname(__DIR__) . '/vendor/autoload.php';
    $course_options = [];
    $member_options = [];
    try {
        $pdo_c = (new \App\Database\Connection())->getPdo();
        if ($pdo_c) {
            $st = $pdo_c->query("SELECT course_id, course_name FROM tbl_course WHERE delete_at IS NULL ORDER BY course_name ASC");
            $course_options = $st->fetchAll(PDO::FETCH_ASSOC);
            $st->closeCursor();
            $su = $pdo_c->query("SELECT user_id, user_firstname, user_lastname FROM tbl_user WHERE delete_at IS NULL ORDER BY user_firstname ASC");
            $member_options = $su->fetchAll(PDO::FETCH_ASSOC);
            $su->closeCursor();
        }
    } catch (\Throwable $e) {}
    $member_label = function ($m) {
        $n = trim(($m['user_firstname'] ?? '') . ' ' . ($m['user_lastname'] ?? ''));
        return $n !== '' ? $n : ('user#' . $m['user_id']);
    };
?>
<?php include "header.php"; ?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <?php include "navbar.php"; ?>

        <div class="px-2">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-3 p-4">
                    <h4 class="mb-0">ใบรับรองผลการสอบ</h4>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-medium">คอร์สเรียน</label>
                            <select class="form-select tom-course" id="f_course">
                                <option value="">ทั้งหมด</option>
                                <?php foreach ($course_options as $c): ?>
                                    <option value="<?php echo (int) $c['course_id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">ผู้เรียน</label>
                            <select class="form-select tom-member" id="f_member">
                                <option value="">ทั้งหมด</option>
                                <?php foreach ($member_options as $m): ?>
                                    <option value="<?php echo (int) $m['user_id']; ?>"><?php echo htmlspecialchars($member_label($m)); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-medium">สถานะการสอบ</label>
                            <select class="form-select" id="f_status">
                                <option value="">ทั้งหมด</option>
                                <option value="1">ผ่าน</option>
                                <option value="0">ไม่ผ่าน</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-medium">การอนุมัติ</label>
                            <select class="form-select" id="f_approve">
                                <option value="">ทั้งหมด</option>
                                <option value="1">อนุมัติ</option>
                                <option value="0">รออนุมัติ</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100" onclick="SearchData()">ค้นหา</button>
                        </div>
                    </div>

                    <div class="default-table-area">
                        <div class="table-responsive">
                            <table class="table align-middle w-100" id="PageTable">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center" style="width:60px;">#</th>
                                        <th scope="col" class="text-nowrap">เลขที่ใบรับรอง</th>
                                        <th scope="col">คอร์สเรียน</th>
                                        <th scope="col" class="text-nowrap">ผู้สอบ</th>
                                        <th scope="col" class="text-nowrap">คะแนนที่ได้</th>
                                        <th scope="col" class="text-center">สถานะ</th>
                                        <th scope="col" class="text-center">การอนุมัติ</th>
                                        <th scope="col" class="text-center" style="width:160px;"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include "footer.php"; ?>
    </div>
</div>

<!-- ===== Modal: ดูไฟล์ยืนยันตัวตนในเอกสาร ===== -->
<div class="modal fade" id="modalManage" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">ไฟล์ยืนยันตัวตนในเอกสาร</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <h6 class="fw-bold mb-2">รูปเอกสารยืนยันตัวตนในใบรับรอง</h6>
                <div id="ManageIdCard" class="mb-4"><div class="text-muted">-</div></div>
                <h6 class="fw-bold mb-2">รูปเอกสารของผู้ใช้ปัจจุบัน</h6>
                <div id="ManageCurrent"><div class="text-muted">-</div></div>
            </div>
        </div>
    </div>
</div>

<?php include "script.php"; ?>

</body>

</html>

<script>
    var certTable = null;

    $(document).ready(function () {
        if (typeof TomSelect !== "undefined") {
            document.querySelectorAll('.tom-course, .tom-member').forEach(function (el) {
                new TomSelect(el, { create: false, allowEmptyOption: true });
            });
        }

        certTable = $("#PageTable").DataTable({
            processing: true, serverSide: true, responsive: true, autoWidth: false,
            pageLength: 10, order: [[0, "desc"]],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: { url: '../template/assets/js/data-table-th.json' },
            ajax: {
                url: "core.php", type: "POST",
                data: function (d) {
                    d.request_state = "list_certificate";
                    d.request_function = "get_list_certificate";
                    d.f_course = $("#f_course").val();
                    d.f_member = $("#f_member").val();
                    d.f_status = $("#f_status").val();
                    d.f_approve = $("#f_approve").val();
                    return d;
                },
                error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
            },
            columns: [
                { data: "no", className: "text-center", orderable: false },
                { data: "cert_no", className: "text-nowrap" },
                { data: "course" },
                { data: "examiner", className: "text-nowrap" },
                { data: "score", className: "text-nowrap" },
                { data: "status", className: "text-center", orderable: false },
                { data: "approve", className: "text-center", orderable: false },
                { data: null, className: "text-center text-nowrap", orderable: false, render: function (row) {
                    return '<button type="button" class="btn btn-sm btn-info text-white me-1" onclick="DownloadCert(' + row.enroll_id + ')">ดาวน์โหลด</button>' +
                           '<button type="button" class="btn btn-sm btn-primary" onclick="OpenManage(' + row.enroll_id + ')">ดำเนินการ</button>';
                } }
            ]
        });
    });

    function SearchData() { if (certTable) { certTable.ajax.reload(); } }

    // ดูใบรับรอง -> เปิดหน้าพรีวิว PDF ในแท็บใหม่
    function DownloadCert(id) {
        window.open("pdf_preview.php?type=certificate&id=" + id, "_blank");
    }

    // ดูไฟล์ยืนยันตัวตนในเอกสาร (เทียบรูปในใบรับรอง vs รูปผู้ใช้ปัจจุบัน)
    function OpenManage(id) {
        $("#ManageIdCard").html('<div class="text-muted">กำลังโหลด...</div>');
        $("#ManageCurrent").html('<div class="text-muted">-</div>');
        new bootstrap.Modal(document.getElementById("modalManage")).show();
        $.ajax({
            type: "POST", url: "core.php",
            data: { request_state: "list_certificate", request_function: "get_certificate", enroll_id: id },
            dataType: "json",
            success: function (r) {
                if (r.result != 1) { Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + r.msg + '</span>', icon: "error" }); return; }
                var imgTag = function (src) {
                    return src ? '<img src="' + src + '" class="img-fluid rounded border" onerror="this.parentNode.innerHTML=\'<div class=&quot;text-muted&quot;>ไม่พบรูป</div>\'">' : '<div class="text-muted">ไม่มีรูป</div>';
                };
                $("#ManageIdCard").html(imgTag(r.data.id_card_image));
                $("#ManageCurrent").html(imgTag(r.data.current_photo));
            },
            error: function (j, e) { ShowErrorAjax(j, e); }
        });
    }
</script>
