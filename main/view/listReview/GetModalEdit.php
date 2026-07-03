<?php

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

use App\Utility\Auth;
use App\Database\Connection;

// fragment นี้ถูกยิงตรง (ไม่ผ่าน main/core.php router) จึงต้องเช็ค token เองที่นี่
// กันดึงข้อมูลรีวิว (ชื่อ/อีเมลผู้รีวิว) โดยไม่ผ่านการยืนยันตัวตน
Auth::requireUserToken();

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
$review_id = isset($data['review_id']) ? (int) $data['review_id'] : 0;

$review = null;
try {
    $pdo = (new Connection())->getPdo();
    if ($pdo && $review_id > 0) {
        $stmt = $pdo->prepare(
            "SELECT r.review_id, r.rating, r.comment, r.is_approved, r.created_at,
                    u.user_firstname, u.user_lastname, u.user_email
             FROM tbl_reviews r
             JOIN tbl_user u ON u.user_id = r.user_id
             WHERE r.review_id = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $review_id]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        $stmt->closeCursor();
    }
} catch (\Throwable $e) {
    $review = null;
}

$esc = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$reviewer_name = $review ? trim(($review['user_firstname'] ?? '') . ' ' . ($review['user_lastname'] ?? '')) : '';
$rating = $review ? (int) $review['rating'] : 0;
$is_approved = $review ? (string) $review['is_approved'] : '1';
?>
    <div class="modal-header">
        <h5 class="modal-title" id="myModalLabel">แก้ไขรีวิว</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body p-4">
        <?php if (!$review): ?>
            <div class="text-center text-danger py-3">ไม่พบรีวิวนี้</div>
        <?php else: ?>
            <form id="formEditReview">
                <input type="hidden" id="edit_review_id" name="review_id" value="<?php echo $esc($review_id); ?>">

                <div class="mb-3">
                    <label class="form-label fw-medium text-secondary">ผู้รีวิว</label>
                    <div class="fw-medium"><?php echo $esc($reviewer_name !== '' ? $reviewer_name : '-'); ?></div>
                    <div class="text-secondary small"><?php echo $esc($review['user_email'] ?? ''); ?></div>
                </div>

                <div class="mb-3">
                    <label for="edit_rating" class="form-label fw-medium">คะแนน (1-5) <span class="text-danger">*</span></label>
                    <select class="form-select" id="edit_rating" name="rating">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo $i === $rating ? 'selected' : ''; ?>><?php echo $i; ?> ดาว</option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="edit_comment" class="form-label fw-medium">ความคิดเห็น <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="edit_comment" name="comment" rows="4"><?php echo $esc($review['comment'] ?? ''); ?></textarea>
                </div>

                <div class="mb-1">
                    <label class="form-label fw-medium d-block">สถานะการแสดงผล <span class="text-danger">*</span></label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_approved" id="approved_on" value="1" <?php echo $is_approved === '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="approved_on">แสดงผลที่หน้าเว็บ</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_approved" id="approved_off" value="0" <?php echo $is_approved !== '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="approved_off">ซ่อน</label>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <?php if ($review): ?>
    <div class="modal-footer p-3">
        <button type="button" class="btn btn-primary px-4" style="width:100%" onclick="UpdateReview()">
            บันทึกข้อมูล
        </button>
    </div>
    <?php endif; ?>

<script>
    function UpdateReview() {
        var comment = $('#edit_comment').val().trim();
        if (comment === '') {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณากรอกข้อความรีวิว</span>', icon: "error", showConfirmButton: false, allowOutsideClick: false, timer: 2000, timerProgressBar: true });
            return;
        }

        var formData = new FormData($('#formEditReview')[0]);
        formData.append('request_state', 'list_review');
        formData.append('request_function', 'update_review');

        $.ajax({
            type: "POST",
            url: "core.php",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    $("#myModal").modal('hide');
                    Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + response.msg + '</span>', icon: "success", showConfirmButton: false, allowOutsideClick: false, timer: 2000, timerProgressBar: true })
                        .then(() => { LoadData(); });
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: false, allowOutsideClick: false, timer: 2000, timerProgressBar: true });
                }
            },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
</script>
