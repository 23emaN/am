<?php
// เพิ่มเอกสารประกอบบทเรียน — insert row ก่อนเพื่อเอา id ไปตั้งชื่อไฟล์ {id}.{ext}

use App\Utility\Auth;
use App\Utility\Response;
use App\Database\Connection;

$access_token = Auth::requireUserToken();
$user_id = $access_token->user_id ?? null;

if (!$user_id) {
    Response::json(0, 'Unauthorized', null);
}

$lesson_id = isset($_POST['lesson_id']) ? (int) $_POST['lesson_id'] : 0;
$file_name = isset($_POST['lesson_file_name']) ? trim($_POST['lesson_file_name']) : '';

if ($lesson_id <= 0) {
    Response::json(0, 'กรุณาเลือกบทเรียน', null);
}
if ($file_name === '') {
    Response::json(0, 'กรุณากรอกชื่อเอกสาร', null);
}
if (empty($_FILES['lesson_file']['name']) || ($_FILES['lesson_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    Response::json(0, 'กรุณาเลือกไฟล์', null);
}

$ext = strtolower(pathinfo($_FILES['lesson_file']['name'], PATHINFO_EXTENSION));
$allowExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'csv'];
if (!in_array($ext, $allowExt, true)) {
    Response::json(0, 'ชนิดไฟล์ไม่รองรับ', null);
}
if ($_FILES['lesson_file']['size'] > 50 * 1024 * 1024) {
    Response::json(0, 'ขนาดไฟล์ต้องไม่เกิน 50 MB', null);
}

$db_instance = new Connection();
$pdo_connect = $db_instance->getPdo();

if (!$pdo_connect) {
    Response::json(0, 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', null);
}

// ตรวจว่าบทเรียนมีจริง
$check = $pdo_connect->prepare("SELECT lesson_id FROM tbl_lesson WHERE lesson_id = :id AND delete_at IS NULL LIMIT 1");
$check->execute([':id' => $lesson_id]);
if (!$check->fetchColumn()) {
    $check->closeCursor();
    Response::json(0, 'ไม่พบบทเรียนนี้', null);
}
$check->closeCursor();

// mime type จริงของไฟล์ (fallback เป็น type ที่ browser ส่งมา)
$mime = @mime_content_type($_FILES['lesson_file']['tmp_name']) ?: ($_FILES['lesson_file']['type'] ?? null);

$rootDir   = dirname(__DIR__, 3);
$uploadDir = $rootDir . '/upload/lesson_file/';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
    Response::json(0, 'ไม่สามารถสร้างโฟลเดอร์อัปโหลดได้', null);
}

try {
    $stmt = $pdo_connect->prepare(
        "INSERT INTO tbl_lesson_file (lesson_id, lesson_file_name, lesson_file_type)
         VALUES (:lid, :name, :type)"
    );
    $stmt->execute([':lid' => $lesson_id, ':name' => $file_name, ':type' => $mime]);
    $file_id = (int) $pdo_connect->lastInsertId();
    $stmt->closeCursor();

    $dest = $uploadDir . $file_id . '.' . $ext;
    if (!move_uploaded_file($_FILES['lesson_file']['tmp_name'], $dest)) {
        // กู้คืน: ลบ row ที่เพิ่ง insert ออก (hard delete เพราะยังไม่มีไฟล์จริง)
        $pdo_connect->prepare("DELETE FROM tbl_lesson_file WHERE lesson_file_id = :id")->execute([':id' => $file_id]);
        Response::json(0, 'อัปโหลดไฟล์ไม่สำเร็จ', null);
    }

    Response::json(1, 'เพิ่มเอกสารสำเร็จ', ['lesson_file_id' => $file_id]);
} catch (Exception $e) {
    error_log('Add Lesson File Error: ' . $e->getMessage());
    Response::json(0, 'เกิดข้อผิดพลาดในการบันทึกข้อมูล', null);
} finally {
    $pdo_connect = null;
}
