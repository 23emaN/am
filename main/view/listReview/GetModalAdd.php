<?php

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

use App\Utility\Auth;
use App\Database\Connection;

// fragment ยิงตรง (ไม่ผ่าน main/core.php router) จึงเช็ค token เองที่นี่ — กันดึงรายชื่อลูกค้าโดยไม่ยืนยันตัวตน
Auth::requireUserToken();

$customers = [];
try {
    $pdo = (new Connection())->getPdo();
    if ($pdo) {
        $stmt = $pdo->query(
            "SELECT user_id, user_firstname, user_lastname, user_email
             FROM tbl_user
             WHERE delete_at IS NULL
             ORDER BY user_firstname ASC, user_lastname ASC"
        );
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    }
} catch (\Throwable $e) {
    $customers = [];
}

$esc = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$cust_label = function ($c) {
    $name  = trim(($c['user_firstname'] ?? '') . ' ' . ($c['user_lastname'] ?? ''));
    $name  = $name !== '' ? $name : ('user#' . $c['user_id']);
    $email = trim((string) ($c['user_email'] ?? ''));
    return $email !== '' ? ($name . ' (' . $email . ')') : $name;
};
$today = date('d/m/Y');
?>
    <div class="modal-header">
        <h5 class="modal-title" id="myModalLabel">เพิ่มรีวิว</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body p-4">
        <form id="formAddReview">

            <div class="mb-3">
                <label class="form-label fw-medium d-block">ผู้รีวิว <span class="text-danger">*</span></label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="reviewer_type" id="rt_user" value="user" checked onchange="AddReviewToggleReviewer()">
                    <label class="form-check-label" for="rt_user">เลือกจากลูกค้าที่มีอยู่</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="reviewer_type" id="rt_custom" value="custom" onchange="AddReviewToggleReviewer()">
                    <label class="form-check-label" for="rt_custom">พิมพ์ชื่อผู้รีวิวเอง</label>
                </div>
            </div>

            <div class="mb-3" id="add_user_wrap">
                <select class="form-select" id="add_user_id" name="user_id">
                    <option value="">--- กรุณาเลือกลูกค้า ---</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?php echo (int) $c['user_id']; ?>"><?php echo $esc($cust_label($c)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3 d-none" id="add_name_wrap">
                <input type="text" class="form-control" id="add_reviewer_name" name="reviewer_name" placeholder="ชื่อผู้รีวิว" maxlength="255">
            </div>

            <div class="mb-3">
                <label for="add_rating" class="form-label fw-medium">คะแนน (1-5) <span class="text-danger">*</span></label>
                <select class="form-select" id="add_rating" name="rating">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php echo $i === 5 ? 'selected' : ''; ?>><?php echo $i; ?> ดาว</option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="add_comment" class="form-label fw-medium">ความคิดเห็น <span class="text-danger">*</span></label>
                <textarea class="form-control" id="add_comment" name="comment" rows="4"></textarea>
            </div>

            <div class="mb-3">
                <label for="add_review_date" class="form-label fw-medium">วันที่รีวิว</label>
                <input type="text" class="form-control" id="add_review_date" name="review_date" value="<?php echo $esc($today); ?>" placeholder="วัน/เดือน/ปี" autocomplete="off">
            </div>

            <div class="mb-1">
                <label class="form-label fw-medium d-block">สถานะการแสดงผล <span class="text-danger">*</span></label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="is_approved" id="add_approved_on" value="1" checked>
                    <label class="form-check-label" for="add_approved_on">แสดงผลที่หน้าเว็บ</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="is_approved" id="add_approved_off" value="0">
                    <label class="form-check-label" for="add_approved_off">ซ่อน</label>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer p-3">
        <button type="button" class="btn btn-primary px-4" style="width:100%" onclick="AddReview()">บันทึกข้อมูล</button>
    </div>

<script>
    // init widget หลัง fragment ถูกแทรกเข้า DOM (ลอกจากหน้า "คอร์สเรียนคงเหลือ")
    (function () {
        if (typeof TomSelect !== "undefined") {
            var el = document.getElementById("add_user_id");
            if (el && !el.tomselect) { new TomSelect(el, { create: false, allowEmptyOption: true }); }
        }
        if (typeof flatpickr !== "undefined") {
            flatpickr("#add_review_date", { dateFormat: "d/m/Y", allowInput: true, maxDate: "today" });
        }
    })();

    // สลับช่อง "เลือกลูกค้า" <-> "พิมพ์ชื่อเอง"
    function AddReviewToggleReviewer() {
        var isCustom = $('input[name="reviewer_type"]:checked').val() === "custom";
        $("#add_name_wrap").toggleClass("d-none", !isCustom);
        $("#add_user_wrap").toggleClass("d-none", isCustom);
    }

    function AddReview() {
        var type = $('input[name="reviewer_type"]:checked').val();
        var comment = $("#add_comment").val().trim();

        if (type === "user" && !$("#add_user_id").val()) {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณาเลือกลูกค้า</span>', icon: "error", showConfirmButton: false, allowOutsideClick: false, timer: 1800, timerProgressBar: true });
            return;
        }
        if (type === "custom" && $("#add_reviewer_name").val().trim() === "") {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณากรอกชื่อผู้รีวิว</span>', icon: "error", showConfirmButton: false, allowOutsideClick: false, timer: 1800, timerProgressBar: true });
            return;
        }
        if (comment === "") {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณากรอกข้อความรีวิว</span>', icon: "error", showConfirmButton: false, allowOutsideClick: false, timer: 1800, timerProgressBar: true });
            return;
        }

        var formData = new FormData($("#formAddReview")[0]);
        formData.append("request_state", "list_review");
        formData.append("request_function", "add_review");

        $.ajax({
            type: "POST",
            url: "core.php",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    $("#myModal").modal("hide");
                    Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + response.msg + '</span>', icon: "success", showConfirmButton: false, allowOutsideClick: false, timer: 2000, timerProgressBar: true })
                        .then(function () { LoadData(); });
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: false, allowOutsideClick: false, timer: 2000, timerProgressBar: true });
                }
            },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
</script>
