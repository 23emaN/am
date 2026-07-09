<?php

use App\Utility\Auth;
use App\Utility\Response;
use App\Database\Connection;
use App\Utility\AwsS3;

$access_token = Auth::requireUserToken();
$user_id = $access_token->user_id ?? null;

if (!$user_id) {
    Response::json(0, 'Unauthorized', null);
}

$db_instance = new Connection();
$pdo_connect = $db_instance->getPdo();

if (!$pdo_connect) {
    Response::json(0, 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', null);
}

/* ---------- helpers: แปลงค่าจากฟอร์มให้พร้อมบันทึก (เหมือน AddCourse) ---------- */
$str = function (string $key): ?string {
    $v = isset($_POST[$key]) ? trim($_POST[$key]) : '';
    return $v === '' ? null : $v;
};
$int = function (string $key): ?int {
    $v = isset($_POST[$key]) ? trim($_POST[$key]) : '';
    return $v === '' ? null : (int) $v;
};
$dec = function (string $key): float {
    $v = isset($_POST[$key]) ? trim($_POST[$key]) : '';
    return $v === '' ? 0.0 : (float) $v;
};
$flag = function (string $key, string $default): string {
    $v = isset($_POST[$key]) ? trim($_POST[$key]) : '';
    return $v === '1' ? '1' : ($v === '0' ? '0' : $default);
};

/* ---------- validate ---------- */
$course_id = $int('course_id');
if ($course_id === null || $course_id <= 0) {
    Response::json(0, 'ไม่พบรหัสคอร์สเรียน', null);
}

$course_name = $str('course_name');
if ($course_name === null) {
    Response::json(0, 'กรุณากรอกชื่อคอร์สเรียน', null);
}

$course_group = $int('course_group');
if ($course_group === null) {
    Response::json(0, 'กรุณาเลือกหมวดหมู่', null);
}

// ตรวจว่าคอร์สมีจริงและยังไม่ถูกลบ
$check = $pdo_connect->prepare("SELECT course_id FROM tbl_course WHERE course_id = :id AND delete_at IS NULL LIMIT 1");
$check->execute([':id' => $course_id]);
$exists = $check->fetchColumn();
$check->closeCursor();
if (!$exists) {
    Response::json(0, 'ไม่พบคอร์สเรียนนี้', null);
}

/* ---------- อัปโหลดรูปหน้าปกใหม่ขึ้น S3 (ถ้ามี) ---------- */
$new_cover = null;
if (!empty($_FILES['course_cover_image']['name']) && ($_FILES['course_cover_image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {

    $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
    $ext = strtolower(pathinfo($_FILES['course_cover_image']['name'], PATHINFO_EXTENSION));

    if (!isset($allowed[$ext])) {
        Response::json(0, 'รองรับเฉพาะไฟล์รูปภาพ (jpg, png, webp, gif)', null);
    }
    if ($_FILES['course_cover_image']['size'] > 5 * 1024 * 1024) {
        Response::json(0, 'ขนาดรูปต้องไม่เกิน 5 MB', null);
    }

    // อัปโหลดขึ้น S3 แล้วเก็บ URL เต็ม; ไม่ลบรูปเก่าใน S3
    $filename = bin2hex(random_bytes(8));
    $s3Result = AwsS3::uploadFileDirectly($_FILES['course_cover_image'], true, 'course', $filename);
    if (isset($s3Result['error'])) {
        Response::json(0, 'อัปโหลดรูปขึ้น S3 ไม่สำเร็จ: ' . $s3Result['error'], null);
    }
    $new_cover = $s3Result['url'];
}

/* ---------- update ---------- */
try {
    $fields = [
        'course_name'            => $course_name,
        'course_type'            => $int('course_type'),
        'course_group'           => $course_group,
        'course_instructor'      => $str('course_instructor'),
        'course_overview'        => $str('course_overview'),
        'course_detail'          => $str('course_detail'),
        'course_demo_link'       => $str('course_demo_link'),
        'course_period'          => $int('course_period'),
        'course_approval_date_1' => $str('course_approval_date_1'),
        'course_approval_date_2' => $str('course_approval_date_2'),
        'course_approval_date_3' => $str('course_approval_date_3'),
        'course_approval_date_4' => $str('course_approval_date_4'),
        'course_code_cpd_1'      => $str('course_code_cpd_1'),
        'course_code_cpd_2'      => $str('course_code_cpd_2'),
        'course_code_cpd_3'      => $str('course_code_cpd_3'),
        'course_code_cpd_4'      => $str('course_code_cpd_4'),
        'course_code_cpa_1'      => $str('course_code_cpa_1'),
        'course_code_cpa_2'      => $str('course_code_cpa_2'),
        'course_code_cpa_3'      => $str('course_code_cpa_3'),
        'course_code_cpa_4'      => $str('course_code_cpa_4'),
        'course_cpd_hour'        => $dec('course_cpd_hour'),
        'course_cpd_ethics'      => $dec('course_cpd_ethics'),
        'course_cpd_other'       => $dec('course_cpd_other'),
        'course_cpa_hour'        => $dec('course_cpa_hour'),
        'course_cpa_ethics'      => $dec('course_cpa_ethics'),
        'course_cpa_other'       => $dec('course_cpa_other'),
        'course_exam_time'       => $int('course_exam_time'),
        'course_minimum_score'   => $int('course_minimum_score'),
        'course_number_exam'     => $int('course_number_exam'),
        'course_number_time'     => $int('course_number_time'),
        'course_price'           => $dec('course_price'),
        'course_promotion'       => $dec('course_promotion'),
        'course_display'         => $flag('course_display', '1'),
        'course_status'          => $flag('course_status', '1'),
        'course_skip'            => $flag('course_skip', '0'),
        'course_otp'             => $flag('course_otp', '0'),
    ];

    // อัปเดตรูปหน้าปกเฉพาะเมื่อมีไฟล์ใหม่ (ไม่งั้นเก็บของเดิม)
    if ($new_cover !== null) {
        $fields['course_cover_image'] = $new_cover;
    }

    $set = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($fields)));
    $sql = "UPDATE tbl_course SET $set WHERE course_id = :course_id AND delete_at IS NULL";

    $stmt = $pdo_connect->prepare($sql);
    foreach ($fields as $col => $val) {
        $stmt->bindValue(':' . $col, $val);
    }
    $stmt->bindValue(':course_id', $course_id, PDO::PARAM_INT);
    $ok = $stmt->execute();
    $stmt->closeCursor();

    if (!$ok) {
        throw new Exception('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
    }

    Response::json(1, 'บันทึกการแก้ไขสำเร็จ', ['course_id' => $course_id]);
} catch (Exception $e) {
    error_log('Update Course Error: ' . $e->getMessage());
    Response::json(0, 'เกิดข้อผิดพลาด', null);
} finally {
    $pdo_connect = null;
}
