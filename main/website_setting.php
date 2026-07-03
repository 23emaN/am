<?php
    $breadcrumbs = [['label' => 'ตั้งค่าเว็บไซต์']];
?>
<?php include "header.php"; ?>

<style>
    /* ช่องกรอกพื้นหลังขาว (ธีมตั้ง .form-control เป็นเทา #F6F7F9) */
    #FormSetting .form-control,
    #FormSetting .form-select,
    #FormSetting .form-control:focus,
    #FormSetting .form-select:focus { background-color: #fff !important; }
    /* Quill ต้องมี height ชัดเจน */
    #editor_text_1, #editor_text_2 { height: 180px; background: #fff; }
    #editor_about_us, #editor_contact_us { height: 160px; background: #fff; }
    .ql-toolbar.ql-snow, .ql-container.ql-snow { border-color: #ced4da; }
    .setting-section-title { letter-spacing: .02em; }
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <?php include "navbar.php"; ?>

        <div class="px-2">
            <div class="card app-card form-card bg-white border-0 rounded-3 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center p-4">
                    <h2 class="mb-0">ตั้งค่าเว็บไซต์</h2>
                </div>

                <div class="card-body p-4">
                    <form id="FormSetting" autocomplete="off" enctype="multipart/form-data">

                        <!-- ===== การชำระเงิน ===== -->
                        <h6 class="fw-bold text-secondary text-uppercase mb-3 setting-section-title">การชำระเงิน</h6>
                        <label class="form-label fw-medium d-block">ช่องทางการชำระเงินที่เปิดให้ลูกค้าใช้</label>
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="pm_credit" name="credit_card" value="1">
                                <label class="form-check-label" for="pm_credit">บัตรเครดิต / บัตรเดบิต</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="pm_qr" name="qr_promptpay" value="1">
                                <label class="form-check-label" for="pm_qr">พร้อมเพย์ (QR PromptPay)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="pm_bank" name="bank_transfer" value="1">
                                <label class="form-check-label" for="pm_bank">โอนเงินผ่านธนาคาร</label>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- ===== ทั่วไป ===== -->
                        <h6 class="fw-bold text-secondary text-uppercase mb-3 setting-section-title">ทั่วไป</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-medium">รหัสหน่วยงาน <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="department_code" placeholder="เช่น 06-330">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">การกดข้ามวิดีโอในบทเรียน</label>
                                <select class="form-select" name="allow_skip_video">
                                    <option value="0">ปิด (กดข้ามไม่ได้)</option>
                                    <option value="1">เปิด (กดข้ามได้)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">การส่ง OTP ระหว่างเรียน</label>
                                <select class="form-select" name="otp_enabled">
                                    <option value="0">ปิดใช้งาน</option>
                                    <option value="1">เปิดใช้งาน</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">คำนวณภาษีมูลค่าเพิ่ม (VAT 7%)</label>
                                <select class="form-select" name="tax_enabled">
                                    <option value="0">ปิดใช้งาน</option>
                                    <option value="1">เปิดใช้งาน</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">เลขผู้เสียภาษี 13 หลัก <small class="text-muted">(ระบบ ETAX(G))</small></label>
                                <input type="text" class="form-control" name="tax_id" maxlength="13" placeholder="0105565002221">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">รหัสสาขา <small class="text-muted">(ระบบ ETAX(G))</small></label>
                                <input type="text" class="form-control" name="branch_code" placeholder="00000">
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- ===== หน้าแรก ===== -->
                        <h6 class="fw-bold text-secondary text-uppercase mb-3 setting-section-title">หน้าแรก</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Youtube ID</label>
                                <input type="text" class="form-control" name="youtube_id" placeholder="เช่น b1E-mcxdtm4">
                                <div class="form-text">ส่วนหลัง <code>watch?v=</code> ของลิงก์ Youtube</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium" for="image_file_input">รูปภาพหน้าแรก <small class="text-muted">(jpg, png, webp, gif · ไม่เกิน 5MB)</small></label>
                                <input type="file" class="form-control" name="image_file" id="image_file_input" accept="image/*">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">ข้อความแถวที่ 1</label>
                                <div id="editor_text_1"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted small">รูปภาพปัจจุบัน</label>
                                <div id="ImgPreviewWrap" class="border rounded-3 p-3 bg-light text-center" style="min-height:140px; display:flex; align-items:center; justify-content:center;">
                                    <span class="text-muted" id="ImgPreviewEmpty">กำลังโหลด...</span>
                                    <img id="ImgPreview" src="" alt="preview" style="max-width:100%; max-height:280px; display:none; border-radius:8px; border:1px solid var(--border);">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">ข้อความแถวที่ 2</label>
                                <div id="editor_text_2"></div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- ===== โซเชียล & ข้อมูลติดต่อ ===== -->
                        <h6 class="fw-bold text-secondary text-uppercase mb-3 setting-section-title">โซเชียล & ข้อมูลติดต่อ</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Facebook</label>
                                <input type="text" class="form-control" name="facebook_link" placeholder="https://facebook.com/...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">X (Twitter)</label>
                                <input type="text" class="form-control" name="x_link" placeholder="https://x.com/...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">LINE</label>
                                <input type="text" class="form-control" name="line_link" placeholder="https://line.me/...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">เกี่ยวกับเรา</label>
                                <div id="editor_about_us"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">ติดต่อเรา</label>
                                <div id="editor_contact_us"></div>
                            </div>
                        </div>

                        <div class="border-top pt-3 mt-4">
                            <button type="submit" class="btn btn-primary w-100 py-2">บันทึกการตั้งค่า</button>
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
    var quillText1 = null, quillText2 = null, quillAbout = null, quillContact = null;

    $(document).ready(function () {
        InitQuills();
        LoadSetting();
        $('#image_file_input').on('change', PreviewNewImage);
    });

    function InitQuills() {
        if (typeof Quill === 'undefined') { return; }
        var opts = {
            theme: 'snow',
            modules: { toolbar: [['bold', 'italic', 'underline'], [{ 'list': 'ordered' }, { 'list': 'bullet' }], [{ 'align': [] }], ['link'], ['clean']] }
        };
        quillText1 = new Quill('#editor_text_1', opts);
        quillText2 = new Quill('#editor_text_2', opts);
        quillAbout = new Quill('#editor_about_us', opts);
        quillContact = new Quill('#editor_contact_us', opts);
    }

    function LoadSetting() {
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#FormSetting"); },
            type: "POST",
            url: "core.php",
            data: { request_state: "website_setting", request_function: "get_setting" },
            dataType: "json",
            success: function (response) {
                if (response.result == 1) { FillForm(response.data); }
                else { Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", confirmButtonText: "ตกลง" }); }
            },
            complete: function () { HideLoadingOverlay("#FormSetting"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    function FillForm(d) {
        var s = (d && d.setting) ? d.setting : null;
        var p = (d && d.payment) ? d.payment : null;
        var f = $("#FormSetting");

        // ช่องทางชำระเงิน: ถ้ายังไม่มีแถว -> ค่าเริ่มต้นเปิดทั้งหมด (ตาม default ของตาราง)
        $('#pm_credit').prop('checked', p ? String(p.credit_card) === "1" : true);
        $('#pm_qr').prop('checked', p ? String(p.qr_promptpay) === "1" : true);
        $('#pm_bank').prop('checked', p ? String(p.bank_transfer) === "1" : true);

        // ทั่วไป
        f.find('[name="department_code"]').val(s ? (s.department_code || "") : "");
        f.find('[name="allow_skip_video"]').val(s ? String(s.allow_skip_video || "0") : "0");
        f.find('[name="otp_enabled"]').val(s ? String(s.otp_enabled || "0") : "0");
        f.find('[name="tax_enabled"]').val(s ? String(s.tax_enabled || "0") : "0");
        f.find('[name="tax_id"]').val(s ? (s.tax_id || "") : "");
        f.find('[name="branch_code"]').val(s ? (s.branch_code || "") : "");

        // หน้าแรก
        f.find('[name="youtube_id"]').val(s ? (s.youtube_id || "") : "");
        if (quillText1) { quillText1.root.innerHTML = s ? (s.text_1 || "") : ""; }
        if (quillText2) { quillText2.root.innerHTML = s ? (s.text_2 || "") : ""; }

        // รูปภาพปัจจุบัน
        if (s && s.image_path) {
            $("#ImgPreviewEmpty").hide();
            $("#ImgPreview").attr("src", "../" + s.image_path).show();
        } else {
            $("#ImgPreview").hide();
            $("#ImgPreviewEmpty").text("ยังไม่มีรูปภาพ").show();
        }

        // โซเชียล & ติดต่อ
        f.find('[name="facebook_link"]').val(s ? (s.facebook_link || "") : "");
        f.find('[name="x_link"]').val(s ? (s.x_link || "") : "");
        f.find('[name="line_link"]').val(s ? (s.line_link || "") : "");
        if (quillAbout) { quillAbout.root.innerHTML = s ? (s.about_us || "") : ""; }
        if (quillContact) { quillContact.root.innerHTML = s ? (s.contact_us || "") : ""; }
    }

    // พรีวิวรูปใหม่ก่อนอัปโหลด
    function PreviewNewImage() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) { $('#ImgPreviewEmpty').hide(); $('#ImgPreview').attr('src', e.target.result).show(); };
            reader.readAsDataURL(file);
        }
    }

    $(document).on('submit', '#FormSetting', function (e) {
        e.preventDefault();

        if (($('[name="department_code"]').val() || '').trim() === '') {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณากรอกรหัสหน่วยงาน</span>', icon: "error", confirmButtonText: "ตกลง" });
            return;
        }

        var fd = new FormData(this);
        // Quill -> ฟิลด์
        fd.set('text_1', quillText1 ? quillText1.root.innerHTML : '');
        fd.set('text_2', quillText2 ? quillText2.root.innerHTML : '');
        fd.set('about_us', quillAbout ? quillAbout.root.innerHTML : '');
        fd.set('contact_us', quillContact ? quillContact.root.innerHTML : '');
        // checkbox -> 0/1 (unchecked จะไม่ถูกส่งใน FormData จึงกำหนดเอง)
        fd.set('credit_card', $('#pm_credit').prop('checked') ? '1' : '0');
        fd.set('qr_promptpay', $('#pm_qr').prop('checked') ? '1' : '0');
        fd.set('bank_transfer', $('#pm_bank').prop('checked') ? '1' : '0');
        fd.append('request_state', 'website_setting');
        fd.append('request_function', 'update_setting');

        Swal.fire({
            title: "ยืนยันการบันทึก?",
            text: "คุณต้องการบันทึกการตั้งค่าเว็บไซต์ใช่หรือไม่",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "ยืนยัน",
            cancelButtonText: "ยกเลิก"
        }).then(function (result) {
            if (!result.isConfirmed) { return; }
            $.ajax({
                beforeSend: function () { ShowLoadingOverlay("#FormSetting"); },
                type: "POST",
                url: "core.php",
                data: fd,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (response) {
                    if (response.result == 1) {
                        Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + response.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1500, timerProgressBar: true, didClose: function () { LoadSetting(); } });
                    } else {
                        Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", confirmButtonText: "ตกลง" });
                    }
                },
                complete: function () { HideLoadingOverlay("#FormSetting"); },
                error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
            });
        });
    });
</script>
