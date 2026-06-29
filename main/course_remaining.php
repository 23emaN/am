<?php
    $breadcrumbs = [['label' => 'คอร์สเรียนคงเหลือในระบบ']];
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
                    <h4 class="mb-0">คอร์สเรียนคงเหลือในระบบ</h4>
                    <button type="button" class="btn btn-success" onclick="OpenAdd()">เพิ่มสิทธิ์การเข้าถึงคอร์สเรียน</button>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-md-5">
                            <label class="form-label fw-medium">คอร์สเรียน</label>
                            <select class="form-select tom-course" id="f_course">
                                <option value="">ทั้งหมด</option>
                                <?php foreach ($course_options as $c): ?>
                                    <option value="<?php echo (int) $c['course_id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-medium">สมาชิก</label>
                            <select class="form-select tom-member" id="f_member">
                                <option value="">ทั้งหมด</option>
                                <?php foreach ($member_options as $m): ?>
                                    <option value="<?php echo (int) $m['user_id']; ?>"><?php echo htmlspecialchars($member_label($m)); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="button" class="btn btn-primary flex-grow-1" onclick="SearchData()">ค้นหา</button>
                            <button type="button" class="btn btn-info text-white" onclick="DownloadReport()" title="ดาวน์โหลด">
                                <span class="material-symbols-outlined align-middle" style="font-size:18px;">download</span>
                            </button>
                        </div>
                    </div>

                    <div class="default-table-area">
                        <div class="table-responsive">
                            <table class="table align-middle w-100" id="PageTable">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center" style="width:60px;">ลำดับ</th>
                                        <th scope="col">ชื่อสมาชิก</th>
                                        <th scope="col">เบอร์โทรติดต่อ</th>
                                        <th scope="col">ชื่อคอร์ส</th>
                                        <th scope="col">วันที่ซื้อ</th>
                                        <th scope="col">วันที่เปิดใช้</th>
                                        <th scope="col">วันหมดอายุ</th>
                                        <th scope="col" class="text-center">อายุคงเหลือ (วัน)</th>
                                        <th scope="col" class="text-end">ราคา</th>
                                        <th scope="col" class="text-center" style="width:90px;"></th>
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

<!-- ===== Modal: เพิ่มสิทธิ์ ===== -->
<div class="modal fade" id="modalAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">เพิ่มสิทธิ์การเข้าถึงคอร์สใหม่</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">คอร์สเรียน</label>
                    <select class="form-select tom-course" id="add_course">
                        <option value="">--- กรุณาเลือกคอร์สเรียน ---</option>
                        <?php foreach ($course_options as $c): ?>
                            <option value="<?php echo (int) $c['course_id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">สมาชิก</label>
                    <select class="form-select tom-member" id="add_member">
                        <option value="">--- กรุณาเลือกลูกค้า ---</option>
                        <?php foreach ($member_options as $m): ?>
                            <option value="<?php echo (int) $m['user_id']; ?>"><?php echo htmlspecialchars($member_label($m)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">วันที่หมดอายุ (หากปล่อยว่างจะเป็นไม่มีกำหนด)</label>
                    <input type="text" class="form-control" id="add_expiry" placeholder="วัน/เดือน/ปี" autocomplete="off">
                </div>
                <div class="alert alert-danger small mb-0">กรุณาตรวจสอบข้อมูลก่อนกดบันทึก จะมีการเพิ่มสิทธิ์พร้อมส่งอีเมลแจ้งผู้ใช้งานทันทีหลังมีการบันทึกข้อมูล</div>
            </div>
            <div class="modal-footer p-3"><button type="button" class="btn btn-primary w-100 BtnAdd" onclick="SubmitAdd()">บันทึก</button></div>
        </div>
    </div>
</div>

<!-- ===== Modal: แก้ไขสิทธิ์ ===== -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">แก้ไขสิทธิ์การเข้าถึงคอร์ส</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <input type="hidden" id="edit_id">
                <div class="mb-3">
                    <label class="form-label fw-medium">สถานะ</label>
                    <select class="form-select" id="edit_status">
                        <option value="1">ให้สิทธิ์การใช้งาน</option>
                        <option value="0">ยกเลิกสิทธิ์การใช้งาน</option>
                    </select>
                </div>
                <div class="mb-3" id="edit_expiry_wrap">
                    <label class="form-label fw-medium">วันที่หมดอายุ (หากปล่อยว่างจะเป็นไม่มีกำหนด)</label>
                    <input type="text" class="form-control" id="edit_expiry" placeholder="วัน/เดือน/ปี" autocomplete="off">
                </div>
                <div class="alert alert-danger small mb-0">หากยกเลิกสิทธิ์การเข้าถึงคอร์สแล้วจะไม่สามารถยกเลิกได้ภายหลัง กรุณาตรวจสอบข้อมูลก่อนดำเนินการ</div>
            </div>
            <div class="modal-footer p-3"><button type="button" class="btn btn-primary w-100 BtnEdit" onclick="SubmitEdit()">บันทึก</button></div>
        </div>
    </div>
</div>

<?php include "script.php"; ?>

</body>

</html>

<script>
    var enrollTable = null;

    $(document).ready(function () {
        // dropdown ค้นหาได้ (tom-select)
        if (typeof TomSelect !== "undefined") {
            document.querySelectorAll('.tom-course, .tom-member').forEach(function (el) {
                new TomSelect(el, { create: false, allowEmptyOption: true });
            });
        }
        if (typeof flatpickr !== "undefined") {
            flatpickr("#add_expiry", { dateFormat: "d/m/Y", allowInput: true });
            flatpickr("#edit_expiry", { dateFormat: "d/m/Y", allowInput: true });
        }

        enrollTable = $("#PageTable").DataTable({
            processing: true, serverSide: true, responsive: true, autoWidth: false,
            pageLength: 10, order: [[0, "desc"]],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: { url: '../template/assets/js/data-table-th.json' },
            ajax: {
                url: "core.php", type: "POST",
                data: function (d) {
                    d.request_state = "list_enrollment";
                    d.request_function = "get_list_enrollment";
                    d.f_course = $("#f_course").val();
                    d.f_member = $("#f_member").val();
                    return d;
                },
                error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
            },
            columns: [
                { data: "no", className: "text-center", orderable: false },
                { data: "member", className: "fw-medium" },
                { data: "phone" },
                { data: "course" },
                { data: "buy_at" },
                { data: "open_at" },
                { data: "expiry" },
                { data: "remain", className: "text-center" },
                { data: "price", className: "text-end" },
                { data: "enroll_id", className: "text-center", orderable: false, render: function (d) {
                    return '<button type="button" class="btn btn-sm btn-info text-white" onclick="OpenEdit(' + d + ')">แก้ไข</button>';
                } }
            ]
        });
    });

    function SearchData() { if (enrollTable) { enrollTable.ajax.reload(); } }

    // ===== เพิ่มสิทธิ์ =====
    function OpenAdd() {
        if ($("#add_course")[0].tomselect) { $("#add_course")[0].tomselect.setValue(""); }
        if ($("#add_member")[0].tomselect) { $("#add_member")[0].tomselect.setValue(""); }
        $("#add_expiry").val("");
        new bootstrap.Modal(document.getElementById("modalAdd")).show();
    }
    function SubmitAdd() {
        var course = $("#add_course").val(), member = $("#add_member").val();
        if (!course) { Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณาเลือกคอร์สเรียน</span>', icon: "warning", showConfirmButton: false, timer: 1500 }); return; }
        if (!member) { Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณาเลือกสมาชิก</span>', icon: "warning", showConfirmButton: false, timer: 1500 }); return; }
        $.ajax({
            beforeSend: function () { ShowLoadingButton('.BtnAdd'); },
            type: "POST", url: "core.php",
            data: { request_state: "list_enrollment", request_function: "add_enrollment", course_id: course, user_id: member, expiry: $("#add_expiry").val() },
            dataType: "json",
            success: function (r) {
                if (r.result == 1) {
                    bootstrap.Modal.getInstance(document.getElementById("modalAdd")).hide();
                    Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + r.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1800 }).then(function () { enrollTable.ajax.reload(); });
                } else { Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + r.msg + '</span>', icon: "error", showConfirmButton: true }); }
            },
            complete: function () { HideLoadingButton('.BtnAdd'); },
            error: function (j, e) { ShowErrorAjax(j, e); }
        });
    }

    // ===== แก้ไขสิทธิ์ =====
    function OpenEdit(id) {
        $.ajax({
            type: "POST", url: "core.php",
            data: { request_state: "list_enrollment", request_function: "get_enrollment", enroll_id: id },
            dataType: "json",
            success: function (r) {
                if (r.result != 1) { Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + r.msg + '</span>', icon: "error" }); return; }
                $("#edit_id").val(r.data.enroll_id);
                $("#edit_status").val("1");
                $("#edit_expiry").val(r.data.expiry || "");
                new bootstrap.Modal(document.getElementById("modalEdit")).show();
            },
            error: function (j, e) { ShowErrorAjax(j, e); }
        });
    }
    function SubmitEdit() {
        $.ajax({
            beforeSend: function () { ShowLoadingButton('.BtnEdit'); },
            type: "POST", url: "core.php",
            data: { request_state: "list_enrollment", request_function: "update_enrollment", enroll_id: $("#edit_id").val(), status: $("#edit_status").val(), expiry: $("#edit_expiry").val() },
            dataType: "json",
            success: function (r) {
                if (r.result == 1) {
                    bootstrap.Modal.getInstance(document.getElementById("modalEdit")).hide();
                    Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + r.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1500 }).then(function () { enrollTable.ajax.reload(); });
                } else { Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + r.msg + '</span>', icon: "error", showConfirmButton: true }); }
            },
            complete: function () { HideLoadingButton('.BtnEdit'); },
            error: function (j, e) { ShowErrorAjax(j, e); }
        });
    }

    // ===== ดาวน์โหลด Excel (ตามฟิลเตอร์) =====
    function DownloadReport() {
        var body = new URLSearchParams({ request_state: "list_enrollment", request_function: "export_report", f_course: $("#f_course").val() || "", f_member: $("#f_member").val() || "" });
        Swal.fire({ title: "กำลังสร้างรายงาน...", allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
        fetch("core.php", { method: "POST", headers: { "Authorization": "Bearer " + (localStorage.getItem("access_token") || "") }, body: body })
            .then(function (res) {
                var ct = res.headers.get("Content-Type") || "";
                if (ct.indexOf("application/json") !== -1) { return res.json().then(function (j) { throw new Error(j.msg || "ดาวน์โหลดไม่สำเร็จ"); }); }
                return res.blob();
            })
            .then(function (blob) {
                Swal.close();
                var url = URL.createObjectURL(blob);
                var a = document.createElement("a"); a.href = url; a.download = "course_remaining.xlsx";
                document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
            })
            .catch(function (err) { Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + err.message + '</span>', icon: "error", showConfirmButton: true }); });
    }
</script>
