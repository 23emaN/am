<?php
    $user_id = isset($_GET['id']) ? preg_replace('/[^0-9]/', '', $_GET['id']) : '';
    $breadcrumbs = [
        ['label' => 'ผู้ใช้/ลูกค้าทั้งหมด', 'url' => 'user'],
        ['label' => 'ผู้ใช้/ลูกค้า #' . ($user_id !== '' ? $user_id : '')],
    ];
?>
<?php include "header.php"; ?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <?php include "navbar.php"; ?>

        <div class="px-2">
            <!--
                NOTE: หน้านี้เป็น UI shell (ยังไม่เชื่อมฐานข้อมูล)
                ค่าในฟอร์มเป็นข้อมูลตัวอย่างเพื่อแสดงเลย์เอาต์ตามแบบเท่านั้น
                ขั้นต่อไป (เมื่อสั่ง) จะเชื่อมด้วย pattern เดิม: ajax POST -> handler ใน main/core/listUser/
            -->

            <!-- การ์ดที่ 1: รายละเอียด + ฟอร์มแก้ไข -->
            <div class="card app-card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="mb-0">รายละเอียดผู้ใช้/ลูกค้า</h4>
                            <span id="userVerifyStatus"></span>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-info text-white" onclick="LoginAsUser('<?php echo $user_id; ?>');">ล็อกอินเข้าเว็บไซต์</button>
                            <button type="button" class="btn btn-warning" onclick="OpenVerifyModal();">ตรวจสอบเอกสารยืนยันตัวตนผู้ใช้</button>
                            <button type="button" class="btn btn-danger" onclick="DeleteUser('<?php echo $user_id; ?>');">ลบบัญชีผู้ใช้</button>
                        </div>
                    </div>

                    <form id="FormEditUser" autocomplete="off">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">คำนำหน้า <span class="text-danger">*</span></label>
                                <select class="form-select" name="user_prefix">
                                    <option value="">- เลือก -</option>
                                    <option value="1">นาย</option>
                                    <option value="2">นาง</option>
                                    <option value="3">นางสาว</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="user_firstname" value="">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="user_lastname" value="">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">อีเมล <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="user_email" value="">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="user_phone" value="">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">เลขบัตรประชาชน <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="user_citizen_id" value="">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">เลขที่ผู้ทำบัญชี</label>
                                <input type="text" class="form-control" name="user_cpd_no" value="">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">เลขที่ผู้สอบบัญชี</label>
                                <input type="text" class="form-control" name="user_cpa_no" value="">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">รหัสผ่าน (กรอกหากต้องการเปลี่ยน)</label>
                                <input type="password" class="form-control" name="user_password" value="" autocomplete="new-password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ยืนยันรหัสผ่าน (กรอกหากต้องการเปลี่ยน)</label>
                                <input type="password" class="form-control" name="user_password_confirm" value="" autocomplete="new-password">
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">ยืนยันการแก้ไขข้อมูล</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>

            <!-- การ์ดที่ 2: แท็บข้อมูลที่เกี่ยวข้อง -->
            <div class="card app-card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">

                    <ul class="nav nav-tabs app-tabs mb-3" id="userTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-order" type="button" role="tab">คำสั่งซื้อ</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-enroll" type="button" role="tab">สิทธิ์เข้าคอร์สเรียน</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-exam" type="button" role="tab">ประวัติการสอบ/ใบรับรอง</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-verify" type="button" role="tab">ประวัติการยืนยันตัวตน</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- แท็บ: คำสั่งซื้อ -->
                        <div class="tab-pane fade show active" id="tab-order" role="tabpanel">
                            <div class="default-table-area">
                            <div class="table-responsive">
                                <table class="table align-middle w-100 user-tab-table" id="TableOrder">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 80px;">ลำดับ</th>
                                            <th>หมายเลขคำสั่งซื้อ</th>
                                            <th>คอร์สเรียน</th>
                                            <th class="text-end">ยอดรวม</th>
                                            <th class="text-center">สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            </div>
                        </div>

                        <!-- แท็บ: สิทธิ์เข้าคอร์สเรียน -->
                        <div class="tab-pane fade" id="tab-enroll" role="tabpanel">
                            <div class="default-table-area">
                            <div class="table-responsive">
                                <table class="table align-middle w-100 user-tab-table" id="TableEnroll">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 80px;">ลำดับ</th>
                                            <th>SKU</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            </div>
                        </div>

                        <!-- แท็บ: ประวัติการสอบ/ใบรับรอง -->
                        <div class="tab-pane fade" id="tab-exam" role="tabpanel">
                            <div class="default-table-area">
                            <div class="table-responsive">
                                <table class="table align-middle w-100 user-tab-table" id="TableExam">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 60px;">#</th>
                                            <th>เลขที่ใบรับรอง</th>
                                            <th>คอร์สเรียน</th>
                                            <th>ผู้สอบ</th>
                                            <th class="text-center">คะแนนที่ได้</th>
                                            <th class="text-center">สถานะ</th>
                                            <th class="text-center">การอนุมัติ</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            </div>
                        </div>

                        <!-- แท็บ: ประวัติการยืนยันตัวตน -->
                        <div class="tab-pane fade" id="tab-verify" role="tabpanel">
                            <div class="default-table-area">
                            <div class="table-responsive">
                                <table class="table align-middle w-100 user-tab-table" id="TableVerify">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 60px;">#</th>
                                            <th>ผู้ดำเนินการ</th>
                                            <th class="text-center">สถานะ</th>
                                            <th>คำอธิบาย</th>
                                            <th>วันและเวลาที่ดำเนินการ</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <?php include "footer.php"; ?>

    </div>
</div>

<!-- Modal: ตรวจสอบเอกสารยืนยันตัวตน -->
<div class="modal fade" id="VerifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content app-card">
            <div class="modal-header">
                <h5 class="modal-title">ยืนยันตัวตนผู้ใช้</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="FormVerify" autocomplete="off">
                    <input type="hidden" name="user_id" id="verify_user_id" value="">

                    <div class="mb-3">
                        <label class="form-label d-block">สถานะการยืนยันตัวตนปัจจุบัน</label>
                        <span id="verifyModalStatus"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ประเภทเอกสาร</label>
                        <input type="text" class="form-control bg-light" value="ยืนยันด้วยบัตรประชาชน" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="verify_citizen_id">หมายเลขเอกสาร</label>
                        <input type="text" class="form-control bg-light" id="verify_citizen_id" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="verify_expiry">วันหมดอายุของเอกสาร</label>
                        <input type="text" class="form-control bg-light" id="verify_expiry" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รูปเอกสาร</label>
                        <div>
                            <img id="verify_id_image" src="" alt="รูปเอกสาร" class="img-fluid w-100" style="object-fit: contain; border-radius: var(--radius-md); border: 1px solid var(--border);">
                            <div id="verify_id_image_empty" class="text-muted small d-none">ไม่มีรูปเอกสาร</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รูปหน้ายืนยัน</label>
                        <div>
                            <img id="verify_photo" src="" alt="รูปหน้ายืนยัน" class="img-fluid w-100" style="object-fit: contain; border-radius: var(--radius-md); border: 1px solid var(--border);">
                            <div id="verify_photo_empty" class="text-muted small d-none">ไม่มีรูปหน้ายืนยัน</div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label d-block">ผลการตรวจสอบเอกสาร <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="approver_citizen" id="result_approve" value="2">
                            <label class="form-check-label" for="result_approve">อนุมัติ (ยืนยันตัวตนสำเร็จ)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="approver_citizen" id="result_reject" value="1">
                            <label class="form-check-label" for="result_reject">ไม่อนุมัติ (ปฏิเสธ)</label>
                        </div>
                    </div>

                    <!-- หมายเหตุ: บังคับกรอกเมื่อเลือก "ไม่อนุมัติ" (เหตุผลการปฏิเสธ) -->
                    <div class="mb-2 d-none" id="remark_wrap">
                        <label class="form-label" for="verify_remark">หมายเหตุ (เหตุผลที่ไม่อนุมัติ) <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="remark" id="verify_remark" rows="2" placeholder="ระบุเหตุผลการปฏิเสธ"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100" onclick="SubmitVerify();">ยืนยันข้อมูลการยืนยันตัวตน</button>
            </div>
        </div>
    </div>
</div>

<?php include "script.php"; ?>

</body>

</html>

<script>
    var USER_ID = "<?php echo $user_id; ?>";
    var AUTO_VERIFY = "<?php echo isset($_GET['verify']) ? '1' : '' ?>";
    var verifyModal = null;

    $(document).ready(function () {
        verifyModal = new bootstrap.Modal(document.getElementById('VerifyModal'));

        // เลือก "ไม่อนุมัติ" -> โชว์ช่องหมายเหตุ (บังคับกรอก) ; "อนุมัติ" -> ซ่อน + ล้างค่า
        $('input[name="approver_citizen"]').on('change', function () {
            if ($(this).val() === '1') {
                $('#remark_wrap').removeClass('d-none');
            } else {
                $('#remark_wrap').addClass('d-none');
                $('#verify_remark').val('');
            }
        });

        // มาจากหน้า "คำขอยืนยันตัวตนผู้ใช้งาน" -> เปิด modal ตรวจเอกสารอัตโนมัติ
        if (USER_ID && AUTO_VERIFY === '1') {
            OpenVerifyModal();
        }

        if (USER_ID) {
            LoadUser();
        }
    });

    // โหลดข้อมูลผู้ใช้มาเติมในฟอร์ม + แท็บ
    function LoadUser() {
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#FormEditUser"); },
            type: "POST",
            url: "core.php",
            data: { request_state: "list_user", request_function: "get_user", user_id: USER_ID },
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    FillForm(response.data.user);
                    FillEnrollTab(response.data.enrollments || []);
                    FillExamTab(response.data.exams || []);
                    FillVerifyTab(response.data.verify_history || []);
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: false, allowOutsideClick: false, timer: 2000, timerProgressBar: true });
                }
            },
            complete: function () { HideLoadingOverlay("#FormEditUser"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    function FillForm(u) {
        if (!u) return;
        var f = $("#FormEditUser");
        // ข้อมูลเก่า: user_prefix ว่าง แต่คำนำหน้าฝังอยู่ในชื่อ -> แยกออกมาใส่ dropdown
        // (แยก นางสาว ก่อน นาง เพราะ "นางสาว" ขึ้นต้นด้วย "นาง")
        var prefix = u.user_prefix ? String(u.user_prefix) : "";
        var firstname = u.user_firstname || "";
        if (!prefix) {
            var pmap = [["นางสาว", "3"], ["นาย", "1"], ["นาง", "2"]];
            for (var i = 0; i < pmap.length; i++) {
                if (firstname.indexOf(pmap[i][0]) === 0) {
                    prefix = pmap[i][1];
                    firstname = firstname.substring(pmap[i][0].length).trim();
                    break;
                }
            }
        }
        f.find('[name="user_prefix"]').val(prefix);
        f.find('[name="user_firstname"]').val(firstname);
        f.find('[name="user_lastname"]').val(u.user_lastname || "");
        f.find('[name="user_email"]').val(u.user_email || "");
        f.find('[name="user_phone"]').val(u.user_phone || "");
        f.find('[name="user_citizen_id"]').val(u.user_citizen_id || "");
        f.find('[name="user_cpd_no"]').val(u.user_cpd_no || "");
        f.find('[name="user_cpa_no"]').val(u.user_cpa_no || "");
        f.find('[name="user_password"]').val("");
        f.find('[name="user_password_confirm"]').val("");
        $("#userVerifyStatus").html(VerifyBadge(u.identity_verified));
    }

    // ป้ายสถานะการยืนยันตัวตน (0=ยังไม่ยืนยัน 1=รอตรวจสอบ 2=ยืนยันแล้ว)
    function VerifyBadge(iv) {
        iv = String(iv || '0');
        if (iv === '2') { return '<span class="badge bg-success">ยืนยันตัวตนแล้ว</span>'; }
        if (iv === '1') { return '<span class="badge bg-warning text-dark">รอตรวจสอบ</span>'; }
        return '<span class="badge bg-secondary">ยังไม่ยืนยันตัวตน</span>';
    }

    // เติมแท็บ "ประวัติการยืนยันตัวตน" จาก tbl_identity_verification_log
    function FillVerifyTab(rows) {
        var html = '';
        if (!rows || rows.length === 0) {
            html = '<tr><td colspan="5" class="text-center text-muted">ไม่มีข้อมูล</td></tr>';
        } else {
            rows.forEach(function (r, i) {
                var act = String(r.action_type || '');
                var badge = act === '1' ? '<span class="badge bg-success">อนุมัติยืนยันตัวตน</span>'
                          : act === '2' ? '<span class="badge bg-danger">ยกเลิกการยืนยัน</span>'
                          : '<span class="badge bg-secondary">-</span>';
                var remark = r.remark ? EscapeHTML(r.remark) : '<span class="text-muted">-</span>';
                html += '<tr>'
                    + '<td class="text-center">' + (i + 1) + '</td>'
                    + '<td>' + EscapeHTML(r.admin_name || '-') + '</td>'
                    + '<td class="text-center">' + badge + '</td>'
                    + '<td>' + remark + '</td>'
                    + '<td class="text-nowrap">' + EscapeHTML(FormatDateTime(r.created_at)) + '</td>'
                    + '</tr>';
            });
        }
        $("#TableVerify tbody").html(html);
    }

    function FormatDateTime(ts) {
        if (!ts) { return '-'; }
        var d = new Date(String(ts).replace(' ', 'T'));
        if (isNaN(d.getTime())) { return ts; }
        var p = function (n) { return ('0' + n).slice(-2); };
        return p(d.getDate()) + '/' + p(d.getMonth() + 1) + '/' + d.getFullYear() + ' ' + p(d.getHours()) + ':' + p(d.getMinutes());
    }

    // เติมแท็บ "สิทธิ์เข้าคอร์สเรียน" (ตารางธรรมดา render ทุกแถวฝั่ง client)
    function FillEnrollTab(rows) {
        var html = '';
        if (!rows || rows.length === 0) {
            html = '<tr><td colspan="2" class="text-center text-muted">ไม่มีข้อมูล</td></tr>';
        } else {
            rows.forEach(function (r, i) {
                html += '<tr>'
                    + '<td class="text-center">' + (i + 1) + '</td>'
                    + '<td>' + EscapeHTML(r.sku || r.course_name || 'ไม่มีข้อมูล') + '</td>'
                    + '</tr>';
            });
        }
        $("#TableEnroll tbody").html(html);
    }

    // เติมแท็บ "ประวัติการสอบ/ใบรับรอง" (ตารางธรรมดา render ทุกแถวฝั่ง client)
    function FillExamTab(rows) {
        var html = '';
        if (!rows || rows.length === 0) {
            html = '<tr><td colspan="7" class="text-center text-muted">ไม่มีข้อมูล</td></tr>';
        } else {
            rows.forEach(function (r, i) {
                var status = (String(r.pass) === '1')
                    ? '<span class="badge bg-success">ผ่าน</span>'
                    : '<span class="badge bg-danger">ไม่ผ่าน</span>';
                html += '<tr>'
                    + '<td class="text-center">' + (i + 1) + '</td>'
                    + '<td>-</td>'
                    + '<td>' + EscapeHTML(r.course_name || '-') + '</td>'
                    + '<td>-</td>'
                    + '<td class="text-center">' + EscapeHTML(r.score != null ? String(r.score) : '-') + '</td>'
                    + '<td class="text-center">' + status + '</td>'
                    + '<td class="text-center">-</td>'
                    + '</tr>';
            });
        }
        $("#TableExam tbody").html(html);
    }

    // บันทึกการแก้ไขข้อมูล
    $(document).on('submit', '#FormEditUser', function (e) {
        e.preventDefault();

        var pwd = $('[name="user_password"]').val();
        var pwd2 = $('[name="user_password_confirm"]').val();
        if (pwd !== pwd2) {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน</span>', icon: "warning", confirmButtonText: "ตกลง" });
            return;
        }

        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#FormEditUser"); },
            type: "POST",
            url: "core.php",
            data: $(this).serialize() + "&request_state=list_user&request_function=update_user",
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + response.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1500, timerProgressBar: true });
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", confirmButtonText: "ตกลง" });
                }
            },
            complete: function () { HideLoadingOverlay("#FormEditUser"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    });

    function DeleteUser(user_id) {
        Swal.fire({
            title: "ลบบัญชีผู้ใช้?",
            html: '<span class="text-secondary">ระบบจะทำการลบบัญชีผู้ใช้นี้ (ลบแบบ soft delete)</span>',
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "ลบบัญชี",
            cancelButtonText: "ยกเลิก",
            confirmButtonColor: "#dc3545"
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                type: "POST",
                url: "core.php",
                data: { request_state: "list_user", request_function: "delete_user", user_id: user_id },
                dataType: "json",
                success: function (response) {
                    if (response.result == 1) {
                        Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + response.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1500, timerProgressBar: true, didClose: function () { window.location.href = "user"; } });
                    } else {
                        Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", confirmButtonText: "ตกลง" });
                    }
                },
                error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
            });
        });
    }

    // ล็อกอินเข้าเว็บไซต์ (cpdth) แทนผู้ใช้ — มินต์ token แล้วเปิดเว็บไซต์เป็นผู้ใช้นั้น
    function LoginAsUser(user_id) {
        Swal.fire({
            title: "เข้าสู่ระบบเว็บไซต์แทนผู้ใช้",
            html: '<span class="text-secondary">จะเปิดเว็บไซต์ (หน้าลูกค้า) ในชื่อผู้ใช้นี้ในแท็บใหม่<br>',
            icon: "warning", showCancelButton: true, confirmButtonText: "เปิดเว็บไซต์", cancelButtonText: "ยกเลิก"
        }).then(function (res) {
            if (!res.isConfirmed) { return; }
            $.ajax({
                type: "POST", url: "core.php",
                data: { request_state: "list_user", request_function: "login_as_user", user_id: user_id },
                dataType: "json",
                success: function (r) {
                    if (r.result != 1) {
                        Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + (r.msg || 'ไม่สำเร็จ') + '</span>', icon: "error" });
                        return;
                    }
                    var token = r.data.token;
                    document.cookie = "access_token=" + token + "; path=/; max-age=25200";
                    try { localStorage.setItem("access_token", token); } catch (e) {}
                    window.open("../../cpdth/index.php", "_blank");
                },
                error: function (j, e) { ShowErrorAjax(j, e); }
            });
        });
    }

    // ===== ตรวจสอบเอกสารยืนยันตัวตน (modal) =====

    // รูป KYC (DB เก็บเป็น "uploads/identity/...") เว็บฝั่งลูกค้า (โฟลเดอร์ cpdth) เป็นคนอัปโหลด
    // โปรเจกต์แอดมินกับ cpdth เป็นโฟลเดอร์พี่น้องกันทั้ง 2 environment:
    //   - local : intern/am/        + intern/cpdth/
    //   - server: public_html/am/backoffice/ + public_html/am/cpdth/
    // หน้าอยู่ใน main/ -> ถอยขึ้น 2 ชั้น (main -> โปรเจกต์ -> โฟลเดอร์แม่) แล้วเข้า cpdth/
    var KYC_BASE = "../../cpdth/";

    // แปลง path รูปที่เก็บใน DB ให้เป็น URL ที่เปิดได้ (ถ้าเป็น URL เต็มอยู่แล้วใช้ตามนั้น)
    function ImageSrc(path) {
        if (!path) return "";
        return /^https?:\/\//.test(path) ? path : KYC_BASE + path;
    }

    // แปลงวันหมดอายุ Y-m-d -> dd/mm/yyyy (ค.ศ.)
    function FormatExpiry(d) {
        if (!d || d === "0000-00-00") return "-";
        var p = String(d).split("-");
        if (p.length !== 3) return d;
        return p[2] + "/" + p[1] + "/" + p[0];
    }

    function OpenVerifyModal() {
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#FormEditUser"); },
            type: "POST",
            url: "core.php",
            data: { request_state: "verify_request", request_function: "get_verify", user_id: USER_ID },
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    FillVerifyModal(response.data.verify);
                    verifyModal.show();
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", confirmButtonText: "ตกลง" });
                }
            },
            complete: function () { HideLoadingOverlay("#FormEditUser"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    function FillVerifyModal(v) {
        if (!v) return;
        $("#verifyModalStatus").html(VerifyBadge(v.identity_verified));
        $("#verify_user_id").val(v.user_id || "");
        $("#verify_citizen_id").val(v.user_citizen_id || "-");
        $("#verify_expiry").val(FormatExpiry(v.id_card_expiry_date));
        $('input[name="approver_citizen"]').prop("checked", false);
        $("#verify_remark").val("");
        $("#remark_wrap").addClass("d-none");

        SetVerifyImage("#verify_id_image", "#verify_id_image_empty", v.id_card_image);
        SetVerifyImage("#verify_photo", "#verify_photo_empty", v.current_photo);
    }

    function SetVerifyImage(imgSel, emptySel, path) {
        var src = ImageSrc(path);
        if (src) {
            $(imgSel).attr("src", src).removeClass("d-none");
            $(emptySel).addClass("d-none");
        } else {
            $(imgSel).attr("src", "").addClass("d-none");
            $(emptySel).removeClass("d-none");
        }
    }

    function SubmitVerify() {
        var result = $('input[name="approver_citizen"]:checked').val();
        if (!result) {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณาเลือกผลการตรวจสอบเอกสาร</span>', icon: "warning", confirmButtonText: "ตกลง" });
            return;
        }

        // ปฏิเสธ ต้องระบุหมายเหตุ (เหตุผล) เสมอ
        if (result === '1' && $('#verify_remark').val().trim() === '') {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณาระบุหมายเหตุการไม่อนุมัติ</span>', icon: "warning", confirmButtonText: "ตกลง" });
            $('#verify_remark').focus();
            return;
        }

        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#FormVerify"); },
            type: "POST",
            url: "core.php",
            data: $("#FormVerify").serialize() + "&request_state=verify_request&request_function=update_verify",
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    verifyModal.hide();
                    Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + response.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1500, timerProgressBar: true, didClose: function () { LoadUser(); } });
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", confirmButtonText: "ตกลง" });
                }
            },
            complete: function () { HideLoadingOverlay("#FormVerify"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
</script>
