<?php
    $breadcrumbs = [
        ['label' => 'คูปองส่วนลด', 'url' => 'coupon'],
        ['label' => 'เพิ่มคูปองส่วนลดใหม่'],
    ];
?>
<?php include "header.php"; ?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <?php include "navbar.php"; ?>

        <div class="px-2">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-header bg-white p-4">
                    <h2 class="mb-0">เพิ่มคูปองส่วนลดใหม่</h2>
                </div>

                <div class="card-body p-4">
                    <form id="FormAddCoupon" autocomplete="off">

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
                                    <input class="form-check-input" type="radio" name="coupon_type" id="type_percent" value="percent" checked>
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
                                    <input class="form-check-input" type="radio" name="coupon_status" id="status_on" value="1" checked>
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
                                <button type="submit" class="btn btn-primary w-100">ยืนยันการเพิ่มคูปองส่วนลด</button>
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
    // สุ่มสร้างรหัสคูปอง (8 ตัวอักษร ตัดตัวที่สับสน I/O/0/1 ออก)
    function GenerateCode() {
        const chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
        let s = "";
        for (let i = 0; i < 8; i++) s += chars.charAt(Math.floor(Math.random() * chars.length));
        $('[name="coupon_code"]').val(s);
    }

    $(document).on('submit', '#FormAddCoupon', function (e) {
        e.preventDefault();
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#FormAddCoupon"); },
            type: "POST",
            url: "core.php",
            data: $(this).serialize() + "&request_state=list_coupon&request_function=add_coupon",
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + response.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1500, timerProgressBar: true, didClose: function () { window.location.href = "coupon"; } });
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", confirmButtonText: "ตกลง" });
                }
            },
            complete: function () { HideLoadingOverlay("#FormAddCoupon"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    });
</script>
