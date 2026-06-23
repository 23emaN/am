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
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                        <h4 class="mb-0">รายละเอียดผู้ใช้/ลูกค้า</h4>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-info text-white" onclick="LoginAsUser('<?php echo $user_id; ?>');">ล็อกอินเข้าเว็บไซต์</button>
                            <button type="button" class="btn btn-warning" onclick="VerifyUser('<?php echo $user_id; ?>');">ดำเนินการยืนยันตัวตนผู้ใช้</button>
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
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">

                    <ul class="nav nav-tabs mb-3" id="userTab" role="tablist">
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

                        <!-- แท็บ: สิทธิ์เข้าคอร์สเรียน -->
                        <div class="tab-pane fade" id="tab-enroll" role="tabpanel">
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

                        <!-- แท็บ: ประวัติการสอบ/ใบรับรอง -->
                        <div class="tab-pane fade" id="tab-exam" role="tabpanel">
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

                        <!-- แท็บ: ประวัติการยืนยันตัวตน -->
                        <div class="tab-pane fade" id="tab-verify" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table align-middle w-100 user-tab-table" id="TableVerify">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 60px;">#</th>
                                            <th>ผู้ดำเนินการ</th>
                                            <th>รายละเอียด</th>
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

        <?php include "footer.php"; ?>

    </div>
</div>

<?php include "script.php"; ?>

</body>

</html>

<script>
    var USER_ID = "<?php echo $user_id; ?>";

    $(document).ready(function () {
        InitTabTables();

        // ปรับความกว้างคอลัมน์เมื่อสลับแท็บ (DataTable ในแท็บที่ซ่อนอยู่จะคำนวณความกว้างผิด)
        $('#userTab button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr('data-bs-target');
            $(target).find('table.user-tab-table').each(function () {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().columns.adjust().responsive.recalc();
                }
            });
        });

        if (USER_ID) {
            LoadUser();
        }
    });

    function InitTabTables() {
        var dtOptions = {
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            language: { url: '../template/assets/js/data-table-th.json' },
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "ทั้งหมด"]]
        };
        $(".user-tab-table").each(function () {
            if (!$.fn.DataTable.isDataTable(this)) {
                $(this).DataTable(dtOptions);
            }
        });
    }

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
        f.find('[name="user_prefix"]').val(u.user_prefix || "");
        f.find('[name="user_firstname"]').val(u.user_firstname || "");
        f.find('[name="user_lastname"]').val(u.user_lastname || "");
        f.find('[name="user_email"]').val(u.user_email || "");
        f.find('[name="user_phone"]').val(u.user_phone || "");
        f.find('[name="user_citizen_id"]').val(u.user_citizen_id || "");
        f.find('[name="user_cpd_no"]').val(u.user_cpd_no || "");
        f.find('[name="user_cpa_no"]').val(u.user_cpa_no || "");
        f.find('[name="user_password"]').val("");
        f.find('[name="user_password_confirm"]').val("");
    }

    // เติมแท็บ "สิทธิ์เข้าคอร์สเรียน"
    function FillEnrollTab(rows) {
        var table = $("#TableEnroll").DataTable();
        table.clear();
        rows.forEach(function (r, i) {
            table.row.add([
                '<div class="text-center">' + (i + 1) + '</div>',
                EscapeHTML(r.sku || r.course_name || 'ไม่มีข้อมูล')
            ]);
        });
        table.draw();
    }

    // เติมแท็บ "ประวัติการสอบ/ใบรับรอง"
    function FillExamTab(rows) {
        var table = $("#TableExam").DataTable();
        table.clear();
        rows.forEach(function (r, i) {
            var status = (String(r.pass) === '1')
                ? '<span class="badge bg-success">ผ่าน</span>'
                : '<span class="badge bg-danger">ไม่ผ่าน</span>';
            table.row.add([
                '<div class="text-center">' + (i + 1) + '</div>',
                '-',
                EscapeHTML(r.course_name || '-'),
                '-',
                '<div class="text-center">' + EscapeHTML(r.score != null ? String(r.score) : '-') + '</div>',
                '<div class="text-center">' + status + '</div>',
                '<div class="text-center">-</div>'
            ]);
        });
        table.draw();
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

    // ล็อกอินเข้าเว็บไซต์ + ยืนยันตัวตน — ยังเป็นโครง รอเชื่อมระบบฝั่งเว็บไซต์
    function LoginAsUser(user_id) {
        Swal.fire({ title: "ล็อกอินเข้าเว็บไซต์", html: '<span class="text-secondary">ฟังก์ชันนี้ยังไม่เปิดใช้งาน (รอเชื่อมระบบฝั่งเว็บไซต์)</span>', icon: "info", confirmButtonText: "ตกลง" });
    }

    function VerifyUser(user_id) {
        Swal.fire({ title: "ยืนยันตัวตนผู้ใช้", html: '<span class="text-secondary">ฟังก์ชันนี้ยังเป็นโครง (ยังไม่มีตารางยืนยันตัวตนใน DB)</span>', icon: "info", confirmButtonText: "ตกลง" });
    }
</script>
