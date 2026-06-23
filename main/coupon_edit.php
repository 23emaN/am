<?php
    $coupon_id = isset($_GET['id']) ? preg_replace('/[^0-9]/', '', $_GET['id']) : '';
    $breadcrumbs = [
        ['label' => 'คูปองส่วนลด', 'url' => 'coupon'],
        ['label' => 'คูปอง #' . ($coupon_id !== '' ? $coupon_id : '')],
    ];
?>
<?php include "header.php"; ?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <?php include "navbar.php"; ?>

        <div class="px-2">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center p-4">
                    <h2 class="mb-0">รายละเอียดคูปองส่วนลด</h2>
                    <button type="button" class="btn btn-danger" onclick="DeleteCoupon('<?php echo $coupon_id; ?>');">ลบคูปอง</button>
                </div>

                <div class="card-body p-4">
                    <form id="FormEditCoupon" autocomplete="off">
                        <input type="hidden" name="coupon_id" value="<?php echo $coupon_id; ?>">

                        <h5 class="mb-3">ข้อมูลคูปอง</h5>
                        <div class="row g-3 mb-2">
                            <div class="col-md-3">
                                <label class="form-label">Code <span class="text-danger">*</span>
                                    <a href="javascript:void(0)" class="small ms-1" onclick="GenerateCode();">(สุ่มสร้างรหัส)</a>
                                </label>
                                <input type="text" class="form-control" name="coupon_code" maxlength="10">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">รายละเอียดคูปอง <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="coupon_detail" maxlength="255">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label d-block">ประเภทส่วนลด <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="coupon_type" id="type_percent" value="percent">
                                    <label class="form-check-label" for="type_percent">เปอร์เซ็นต์</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="coupon_type" id="type_fixed" value="fixed">
                                    <label class="form-check-label" for="type_fixed">จำนวนเงิน</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">จำนวนส่วนลด <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" name="coupon_no" placeholder="0">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label d-block">สถานะ <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="coupon_status" id="status_on" value="1">
                                    <label class="form-check-label" for="status_on">เปิดใช้งาน</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="coupon_status" id="status_off" value="0">
                                    <label class="form-check-label" for="status_off">ฉบับร่าง/ปิดใช้งาน</label>
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3 mt-4">เงื่อนไขเพิ่มเติม</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">ลิมิตการใช้ (ทั้งคูปอง)</label>
                                <input type="number" min="0" class="form-control" name="coupon_limit">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ลิมิตการใช้ (ต่อผู้ใช้หนึ่งคน)</label>
                                <input type="number" min="0" class="form-control" name="coupon_limit_person">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ยอดรวมขั้นต่ำในการใช้คูปอง</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="coupon_min">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ส่วนลดสูงสุด (กรณีคิดเป็นเปอร์เซ็นต์)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="coupon_max">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">เริ่มใช้เมื่อ</label>
                                <input type="date" class="form-control" name="coupon_start">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">สิ้นสุดเมื่อ</label>
                                <input type="date" class="form-control" name="coupon_end">
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary w-100">ยืนยันการแก้ไขคูปอง</button>
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
    var COUPON_ID = "<?php echo $coupon_id; ?>";

    $(document).ready(function () {
        if (COUPON_ID) LoadCoupon();
    });

    function GenerateCode() {
        const chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
        let s = "";
        for (let i = 0; i < 8; i++) s += chars.charAt(Math.floor(Math.random() * chars.length));
        $('[name="coupon_code"]').val(s);
    }

    function LoadCoupon() {
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#FormEditCoupon"); },
            type: "POST",
            url: "core.php",
            data: { request_state: "list_coupon", request_function: "get_coupon", coupon_id: COUPON_ID },
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    FillForm(response.data.coupon);
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: false, allowOutsideClick: false, timer: 2000, timerProgressBar: true });
                }
            },
            complete: function () { HideLoadingOverlay("#FormEditCoupon"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    function FillForm(c) {
        if (!c) return;
        var f = $("#FormEditCoupon");
        f.find('[name="coupon_code"]').val(c.coupon_code || "");
        f.find('[name="coupon_detail"]').val(c.coupon_detail || "");
        f.find('[name="coupon_type"][value="' + (c.coupon_type || "percent") + '"]').prop("checked", true);
        f.find('[name="coupon_no"]').val(c.coupon_no || "");
        f.find('[name="coupon_status"][value="' + (String(c.coupon_status) === "1" ? "1" : "0") + '"]').prop("checked", true);
        f.find('[name="coupon_limit"]').val(c.coupon_limit || "");
        f.find('[name="coupon_limit_person"]').val(c.coupon_limit_person || "");
        f.find('[name="coupon_min"]').val(c.coupon_min || "");
        f.find('[name="coupon_max"]').val(c.coupon_max || "");
        f.find('[name="coupon_start"]').val((c.coupon_start && c.coupon_start !== "0000-00-00") ? c.coupon_start : "");
        f.find('[name="coupon_end"]').val((c.coupon_end && c.coupon_end !== "0000-00-00") ? c.coupon_end : "");
    }

    $(document).on('submit', '#FormEditCoupon', function (e) {
        e.preventDefault();
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#FormEditCoupon"); },
            type: "POST",
            url: "core.php",
            data: $(this).serialize() + "&request_state=list_coupon&request_function=update_coupon",
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + response.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1500, timerProgressBar: true });
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", confirmButtonText: "ตกลง" });
                }
            },
            complete: function () { HideLoadingOverlay("#FormEditCoupon"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    });

    function DeleteCoupon(coupon_id) {
        Swal.fire({
            title: "ลบคูปอง?",
            html: '<span class="text-secondary">ระบบจะลบคูปองนี้ (soft delete)</span>',
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "ลบคูปอง",
            cancelButtonText: "ยกเลิก",
            confirmButtonColor: "#dc3545"
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                type: "POST",
                url: "core.php",
                data: { request_state: "list_coupon", request_function: "delete_coupon", coupon_id: coupon_id },
                dataType: "json",
                success: function (response) {
                    if (response.result == 1) {
                        Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + response.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1500, timerProgressBar: true, didClose: function () { window.location.href = "coupon"; } });
                    } else {
                        Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", confirmButtonText: "ตกลง" });
                    }
                },
                error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
            });
        });
    }
</script>
