<?php
    $user_id = isset($_GET['id']) ? preg_replace('/[^0-9]/', '', $_GET['id']) : '';
    $breadcrumbs = [
        ['label' => 'ผู้ดูแลระบบทั้งหมด', 'url' => 'admin'],
        ['label' => 'รายละเอียดผู้ดูแลระบบ #' . ($user_id !== '' ? $user_id : '')],
    ];

    // ป้ายสิทธิ์การใช้งาน (skeleton ตัวอย่าง — ยังไม่เชื่อมระบบสิทธิ์)
    $permission_labels = [
        'ข้อมูลหน้าแรก', 'คอร์สเรียน', 'คอร์สเรียนคงเหลือ',
        'ตอบคำถามจากผู้เรียน', 'คำสั่งซื้อคอร์สเรียน', 'ยืนยันการชำระเงิน',
        'ใบรับรองผลการสอบ', 'ผู้ใช้/ลูกค้า', 'ประวัติการยืนยันตัวตน',
        'คูปองส่วนลด', 'แบนเนอร์', 'ตั้งค่าเว็บไซต์',
        'ผู้ดูแลระบบ', 'รายงาน/เอกสาร', 'คำขอยืนยันตัวตนผู้ใช้งาน',
        'รอยืนยันการซื้อ',
    ];
?>
<?php include "header.php"; ?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <?php include "navbar.php"; ?>

        <div class="px-2">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-header bg-white p-4">
                    <h2 class="mb-0">รายละเอียดผู้ดูแลระบบ</h2>
                </div>

                <div class="card-body p-4">
                    <form id="FormEditAdmin" autocomplete="off">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="admin_name" value="">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">อีเมล <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="user_email" value="">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">รหัสผ่าน (กรอกหากต้องการเปลี่ยน)</label>
                                <input type="password" class="form-control" name="user_password" value="" autocomplete="new-password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ยืนยันรหัสผ่าน (กรอกหากต้องการเปลี่ยน)</label>
                                <input type="password" class="form-control" name="user_password_confirm" value="" autocomplete="new-password">
                            </div>

                            <!-- สิทธิ์การใช้งาน: skeleton ตัวอย่างเท่านั้น (ยังไม่บันทึก/เชื่อมระบบสิทธิ์) -->
                            <div class="col-12">
                                <label class="form-label d-block mb-2">สิทธิ์การใช้งาน</label>
                                <div class="row g-2">
                                    <?php foreach ($permission_labels as $i => $label): ?>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="perm_<?php echo $i; ?>" checked disabled>
                                                <label class="form-check-label" for="perm_<?php echo $i; ?>"><?php echo htmlspecialchars($label); ?></label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-primary w-100">ยืนยันการแก้ไขข้อมูล</button>
                            </div>
                        </div>
                    </form>
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
    var ADMIN_ID = "<?php echo $user_id; ?>";

    $(document).ready(function () {
        if (ADMIN_ID) LoadAdmin();
    });

    function LoadAdmin() {
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#FormEditAdmin"); },
            type: "POST",
            url: "core.php",
            data: { request_state: "list_admin", request_function: "get_admin", user_id: ADMIN_ID },
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    FillForm(response.data.admin);
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: false, allowOutsideClick: false, timer: 2000, timerProgressBar: true });
                }
            },
            complete: function () { HideLoadingOverlay("#FormEditAdmin"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    function FillForm(a) {
        if (!a) return;
        var f = $("#FormEditAdmin");
        f.find('[name="admin_name"]').val(a.full_name || "");
        f.find('[name="user_email"]').val(a.user_email || "");
        f.find('[name="user_password"]').val("");
        f.find('[name="user_password_confirm"]').val("");
    }

    $(document).on('submit', '#FormEditAdmin', function (e) {
        e.preventDefault();

        var pwd  = $('[name="user_password"]').val();
        var pwd2 = $('[name="user_password_confirm"]').val();
        if (pwd !== pwd2) {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน</span>', icon: "warning", confirmButtonText: "ตกลง" });
            return;
        }

        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#FormEditAdmin"); },
            type: "POST",
            url: "core.php",
            data: $(this).serialize() + "&request_state=list_admin&request_function=update_admin",
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + response.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1500, timerProgressBar: true });
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", confirmButtonText: "ตกลง" });
                }
            },
            complete: function () { HideLoadingOverlay("#FormEditAdmin"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    });
</script>
