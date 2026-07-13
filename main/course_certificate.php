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
            <div class="card app-card bg-white border-0 rounded-3 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-3 p-4">
                    <h4 class="mb-0">ใบรับรองผลการสอบ</h4>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-md-3">
                            <label for="f_course" class="form-label fw-medium">คอร์สเรียน</label>
                            <select class="form-select tom-course" id="f_course">
                                <option value="">ทั้งหมด</option>
                                <?php foreach ($course_options as $c): ?>
                                    <option value="<?php echo (int) $c['course_id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="f_member" class="form-label fw-medium">ผู้เรียน</label>
                            <select class="form-select tom-member" id="f_member">
                                <option value="">ทั้งหมด</option>
                                <?php foreach ($member_options as $m): ?>
                                    <option value="<?php echo (int) $m['user_id']; ?>"><?php echo htmlspecialchars($member_label($m)); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="f_status" class="form-label fw-medium">สถานะการสอบ</label>
                            <select class="form-select" id="f_status">
                                <option value="">ทั้งหมด</option>
                                <option value="1">ผ่าน</option>
                                <option value="0">ไม่ผ่าน</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="f_approve" class="form-label fw-medium">การอนุมัติ</label>
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

                    <!-- ตาราง + pagination render จาก view/listCertificate/ViewData.php -->
                    <div id="result_box"></div>
                </div>
            </div>
        </div>

        <?php include "footer.php"; ?>
    </div>
</div>

<!-- ===== Modal 1: ดำเนินการอนุมัติออกใบรับรองการสอบ (เมื่อยัง "รออนุมัติ") ===== -->
<div class="modal fade" id="modalApprove" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">ดำเนินการอนุมัติออกใบรับรองการสอบ</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <table class="table table-sm mb-0 align-middle">
                            <tbody>
                                <tr><td class="text-secondary" style="width:42%">ผู้สอบ</td><td class="fw-medium" id="ap_fullname">-</td></tr>
                                <tr><td class="text-secondary">เลขที่บัตรประชาชน</td><td class="fw-medium" id="ap_citizen">-</td></tr>
                                <tr><td class="text-secondary">เลขที่ผู้ทำบัญชี</td><td class="fw-medium" id="ap_cpd">-</td></tr>
                                <tr><td class="text-secondary">เลขที่ผู้สอบบัญชี</td><td class="fw-medium" id="ap_cpa">-</td></tr>
                                <tr><td class="text-secondary">หมายเลขโทรศัพท์</td><td class="fw-medium" id="ap_phone">-</td></tr>
                                <tr><td class="text-secondary">อีเมล</td><td class="fw-medium" id="ap_email">-</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm mb-0 align-middle">
                            <tbody>
                                <tr><td class="text-secondary" style="width:42%">คอร์สเรียน</td><td class="fw-medium" id="ap_course">-</td></tr>
                                <tr><td class="text-secondary">หมายเลขใบรับรอง</td><td class="fw-medium" id="ap_certno">-</td></tr>
                                <tr><td class="text-secondary">จำนวนข้อ / จำนวนข้อที่ทำ</td><td class="fw-medium" id="ap_num">-</td></tr>
                                <tr><td class="text-secondary">คะแนนขั้นต่ำ</td><td class="fw-medium" id="ap_min">-</td></tr>
                                <tr><td class="text-secondary">คะแนนที่ได้รับ</td><td class="fw-medium" id="ap_score">-</td></tr>
                                <tr><td class="text-secondary">คิดเป็นเปอร์เซ็นต์</td><td class="fw-medium" id="ap_percent">-</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="alert alert-danger mt-3 mb-0 py-2 px-3 small">กรุณาตรวจสอบข้อมูลก่อนยืนยันการทำรายการ ไม่สามารถเปลี่ยนแปลงสถานะได้ในภายหลัง</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100 BtnApprove" onclick="ConfirmApprove()">ยืนยันการออกใบรับรอง</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== Modal 2: ปรับปรุงไฟล์ยืนยันตัวตนในเอกสาร (เมื่อ "อนุมัติแล้ว") ===== -->
<div class="modal fade" id="modalIdentity" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">ปรับปรุงไฟล์ยืนยันตัวตนในเอกสาร</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <h6 class="fw-bold mb-2">รูปเอกสารยืนยันตัวตนในใบรับรอง</h6>
                <div id="ManageIdCard" class="mb-4"><div class="text-muted">-</div></div>
                <h6 class="fw-bold mb-2">รูปเอกสารของผู้ใช้ปัจจุบัน</h6>
                <div id="ManageCurrent"><div class="text-muted">-</div></div>
                <div class="alert alert-danger mt-3 mb-0 py-2 px-3 small">กรุณาตรวจสอบข้อมูลก่อนยืนยันการทำรายการ</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100 BtnUpdateIdentity" onclick="ConfirmUpdateIdentity()">ยืนยันการอัปเดต</button>
            </div>
        </div>
    </div>
</div>

<?php include "script.php"; ?>

</body>

</html>

<script>
    var currentEnrollId = 0;
    var certPage = 1;

    $(document).ready(function () {
        if (typeof TomSelect !== "undefined") {
            document.querySelectorAll('.tom-course, .tom-member').forEach(function (el) {
                new TomSelect(el, { create: false, allowEmptyOption: true });
            });
        }
        GetData(1);
    });

    function SearchData() { GetData(1); }

    // สเต็ป 1: ดึงข้อมูล (JSON) จาก handler
    function GetData(page) {
        page = page || 1;
        certPage = page;
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#result_box"); },
            type: "POST", url: "core.php",
            data: {
                request_state: "list_certificate",
                request_function: "get_list_certificate",
                f_course: $("#f_course").val(),
                f_member: $("#f_member").val(),
                f_status: $("#f_status").val(),
                f_approve: $("#f_approve").val(),
                page: page
            },
            dataType: "json",
            success: function (r) {
                if (r.result == 1) {
                    view_data(r.data);
                } else {
                    $("#result_box").html('');
                    HideLoadingOverlay("#result_box");
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + (r.msg || 'ไม่สามารถโหลดข้อมูลได้') + '</span>', icon: "error" });
                }
            },
            complete: function () { HideLoadingOverlay("#result_box"); },
            error: function (j, e) { ShowErrorAjax(j, e); }
        });
    }

    // สเต็ป 2: ส่งข้อมูลไป render เป็น HTML แล้วแปะใน #result_box
    function view_data(payload) {
        $.ajax({
            type: "POST", url: "view/listCertificate/ViewData.php",
            data: {
                data:     payload.list,
                total:    payload.total,
                page:     payload.page,
                per_page: payload.per_page
            },
            dataType: "html",
            success: function (html) { $("#result_box").html(html); HideLoadingOverlay("#result_box"); },
            complete: function () { HideLoadingOverlay("#result_box"); },
            error: function (j, e) { ShowErrorAjax(j, e); }
        });
    }

    // ดูใบรับรอง -> ดึงเลขทะเบียนของผู้ใช้ก่อน แล้วให้เลือกเฉพาะประเภทที่ "มีเลข"
    //   ผู้ทำบัญชี (cpd) = user_cpd_no, ผู้สอบบัญชี (cpa) = user_cpa_no
    //   ถ้าคอลัมน์ไหนว่าง จะไม่โชว์ประเภทนั้นใน dropdown
    // ประเภทที่เลือกจะเปลี่ยนคำในใบรับรอง (เลขทะเบียน/รหัสหลักสูตร/ข้อความ "สำหรับผู้ทำบัญชี/ผู้สอบบัญชี")
    function DownloadCert(id) {
        $.ajax({
            type: "POST", url: "core.php",
            data: { request_state: "list_certificate", request_function: "get_certificate", enroll_id: id },
            dataType: "json",
            success: function (r) {
                if (r.result != 1) {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + (r.msg || 'ไม่สามารถโหลดข้อมูลได้') + '</span>', icon: "error" });
                    return;
                }
                // สร้างตัวเลือกเฉพาะประเภทที่ผู้ใช้มีเลขทะเบียน
                var opts = {};
                if (String(r.data.cpd_no || '').trim() !== '') { opts.cpd = "ผู้ทำบัญชี"; }
                if (String(r.data.cpa_no || '').trim() !== '') { opts.cpa = "ผู้สอบบัญชี"; }
                var keys = Object.keys(opts);

                if (keys.length === 0) {
                    Swal.fire({ title: "ไม่สามารถออกใบรับรองได้", html: '<span class="text-secondary">ผู้ใช้นี้ยังไม่มีเลขที่ผู้ทำบัญชีหรือเลขที่ผู้สอบบัญชี</span>', icon: "warning" });
                    return;
                }
                // แสดง modal ให้เลือกเสมอ (แม้มีประเภทเดียว) — โชว์เฉพาะตัวเลือกที่มีเลข
                Swal.fire({
                    title: "เลือกประเภทใบรับรอง",
                    input: "select",
                    inputOptions: opts,
                    inputValue: keys[0],
                    showCancelButton: true,
                    confirmButtonText: "ออกใบรับรอง",
                    cancelButtonText: "ยกเลิก",
                    inputValidator: function (v) { if (!v) { return "กรุณาเลือกประเภทใบรับรอง"; } }
                }).then(function (res) {
                    if (!res.isConfirmed) { return; }
                    OpenCertPreview(id, res.value);
                });
            },
            error: function (j, e) { ShowErrorAjax(j, e); }
        });
    }

    // เปิดหน้าพรีวิว PDF ใบรับรองตามประเภทที่เลือก
    function OpenCertPreview(id, certType) {
        window.open("pdf_preview.php?type=certificate&id=" + id + "&cert_type=" + certType, "_blank");
    }

    // ดำเนินการ: ดึงข้อมูลแล้วเลือกโมดัลตามสถานะอนุมัติ
    // ยังไม่อนุมัติ (cert_approved=0) -> โมดัลอนุมัติออกใบรับรอง
    // อนุมัติแล้ว (cert_approved=1)   -> โมดัลปรับปรุงไฟล์ยืนยันตัวตน
    function OpenManage(id) {
        currentEnrollId = id;
        $.ajax({
            type: "POST", url: "core.php",
            data: { request_state: "list_certificate", request_function: "get_certificate", enroll_id: id },
            dataType: "json",
            success: function (r) {
                if (r.result != 1) { Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + r.msg + '</span>', icon: "error" }); return; }
                if (r.data.cert_approved == 1) { OpenIdentityModal(r.data); }
                else { OpenApproveModal(r.data); }
            },
            error: function (j, e) { ShowErrorAjax(j, e); }
        });
    }

    function OpenApproveModal(d) {
        var dash = function (v) { return (v === null || v === undefined || v === '') ? '-' : v; };
        $("#ap_fullname").text(dash(d.fullname));
        $("#ap_citizen").text(dash(d.citizen_id));
        $("#ap_cpd").text(dash(d.cpd_no));
        $("#ap_cpa").text(dash(d.cpa_no));
        $("#ap_phone").text(dash(d.phone));
        $("#ap_email").text(dash(d.email));
        $("#ap_course").text(dash(d.course_name));
        $("#ap_certno").text("รอดำเนินการอนุมัติ");
        $("#ap_num").text(d.num_exam + " ข้อ / " + d.num_exam + " ข้อ");
        $("#ap_min").text(d.min_score + " คะแนน");
        $("#ap_score").text((d.score === null ? "-" : d.score) + " คะแนน");
        $("#ap_percent").text((d.percent === null ? "-" : d.percent) + " %");
        new bootstrap.Modal(document.getElementById("modalApprove")).show();
    }

    function OpenIdentityModal(d) {
        var imgTag = function (src) {
            return src ? '<img src="' + src + '" class="img-fluid rounded border" onerror="this.parentNode.innerHTML=\'<div class=&quot;text-muted&quot;>ไม่พบรูป</div>\'">' : '<div class="text-muted">ไม่มีรูป</div>';
        };
        $("#ManageIdCard").html(imgTag(d.id_card_image));
        $("#ManageCurrent").html(imgTag(d.current_photo));
        new bootstrap.Modal(document.getElementById("modalIdentity")).show();
    }

    // ยืนยันการออกใบรับรอง -> อนุมัติ (set enroll_is_completed='1')
    function ConfirmApprove() {
        if (!currentEnrollId) { return; }
        Swal.fire({
            title: "ยืนยันการออกใบรับรอง",
            html: '<span class="text-secondary">ยืนยันอนุมัติออกใบรับรองรายการนี้? ไม่สามารถเปลี่ยนแปลงสถานะได้ในภายหลัง</span>',
            icon: "warning", showCancelButton: true, confirmButtonText: "ยืนยัน", cancelButtonText: "ยกเลิก"
        }).then(function (res) {
            if (!res.isConfirmed) { return; }
            ShowLoadingButton('.BtnApprove');
            $.ajax({
                type: "POST", url: "core.php",
                data: { request_state: "list_certificate", request_function: "approve_certificate", enroll_id: currentEnrollId },
                dataType: "json",
                success: function (r) {
                    HideLoadingButton('.BtnApprove');
                    if (r.result == 1) {
                        bootstrap.Modal.getInstance(document.getElementById("modalApprove")).hide();
                        Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + r.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1500 })
                            .then(function () { GetData(certPage); });
                    } else {
                        Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + r.msg + '</span>', icon: "error" });
                    }
                },
                error: function (j, e) { HideLoadingButton('.BtnApprove'); ShowErrorAjax(j, e); }
            });
        });
    }

    // ยืนยันการอัปเดต -> ปรับรูปยืนยันตัวตนในใบรับรองให้เป็นรูปปัจจุบัน
    function ConfirmUpdateIdentity() {
        if (!currentEnrollId) { return; }
        Swal.fire({
            title: "ยืนยันการอัปเดต",
            html: '<span class="text-secondary">ปรับปรุงรูปยืนยันตัวตนในใบรับรองให้เป็นรูปเอกสารปัจจุบัน?</span>',
            icon: "warning", showCancelButton: true, confirmButtonText: "ยืนยัน", cancelButtonText: "ยกเลิก"
        }).then(function (res) {
            if (!res.isConfirmed) { return; }
            ShowLoadingButton('.BtnUpdateIdentity');
            $.ajax({
                type: "POST", url: "core.php",
                data: { request_state: "list_certificate", request_function: "update_identity", enroll_id: currentEnrollId },
                dataType: "json",
                success: function (r) {
                    HideLoadingButton('.BtnUpdateIdentity');
                    if (r.result == 1) {
                        bootstrap.Modal.getInstance(document.getElementById("modalIdentity")).hide();
                        Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + r.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1500 });
                    } else {
                        Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + r.msg + '</span>', icon: "error" });
                    }
                },
                error: function (j, e) { HideLoadingButton('.BtnUpdateIdentity'); ShowErrorAjax(j, e); }
            });
        });
    }
</script>
