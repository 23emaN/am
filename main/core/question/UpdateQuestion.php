<?php
// แก้ไขคำถามระหว่างรับชม + ตัวเลือก (แทนที่ตัวเลือกเดิมทั้งชุด)

use App\Utility\Auth;
use App\Utility\Response;
use App\Database\Connection;

$access_token = Auth::requireUserToken();
$user_id = $access_token->user_id ?? null;

if (!$user_id) {
    Response::json(0, 'Unauthorized', null);
}

$question_id   = isset($_POST['question_id']) ? (int) $_POST['question_id'] : 0;
$question_text = isset($_POST['question_text']) ? trim($_POST['question_text']) : '';
$choices       = isset($_POST['choice_text']) && is_array($_POST['choice_text']) ? $_POST['choice_text'] : [];
$correct       = isset($_POST['correct']) ? (int) $_POST['correct'] : 0;

if ($question_id <= 0) {
    Response::json(0, 'ไม่พบรหัสคำถาม', null);
}
if ($question_text === '') {
    Response::json(0, 'กรุณากรอกคำถาม', null);
}

$valid = [];
foreach ($choices as $i => $c) {
    $c = trim((string)$c);
    if ($c !== '') { $valid[$i + 1] = $c; }
}
if (count($valid) < 2) {
    Response::json(0, 'กรุณากรอกตัวเลือกอย่างน้อย 2 ข้อ', null);
}
if (!isset($valid[$correct])) {
    Response::json(0, 'กรุณาเลือกคำตอบที่ถูกต้อง', null);
}

$db_instance = new Connection();
$pdo_connect = $db_instance->getPdo();

if (!$pdo_connect) {
    Response::json(0, 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', null);
}

$check = $pdo_connect->prepare("SELECT question_id FROM tbl_question WHERE question_id = :id AND delete_at IS NULL LIMIT 1");
$check->execute([':id' => $question_id]);
if (!$check->fetchColumn()) {
    $check->closeCursor();
    Response::json(0, 'ไม่พบคำถามนี้', null);
}
$check->closeCursor();

/* ---------- อัปโหลดไฟล์ใหม่ (ถ้ามี) ---------- */
$rootDir   = dirname(__DIR__, 3);
$uploadDir = $rootDir . '/upload/question/';
$saveFile = function (string $field, array $allowExt) use ($uploadDir): ?string {
    if (empty($_FILES[$field]['name']) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowExt, true)) {
        Response::json(0, 'ชนิดไฟล์ไม่รองรับ (' . $field . ')', null);
    }
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        Response::json(0, 'ไม่สามารถสร้างโฟลเดอร์อัปโหลดได้', null);
    }
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $name)) {
        Response::json(0, 'อัปโหลดไฟล์ไม่สำเร็จ (' . $field . ')', null);
    }
    return 'upload/question/' . $name;
};
$image_path = $saveFile('question_image', ['jpg', 'jpeg', 'png', 'webp', 'gif']);
$file_path  = $saveFile('question_file', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'jpg', 'jpeg', 'png']);

try {
    $pdo_connect->beginTransaction();

    // อัปเดตคำถาม (รูป/ไฟล์อัปเดตเฉพาะเมื่อมีไฟล์ใหม่)
    $sql = "UPDATE tbl_question SET question_text = :text";
    $params = [':text' => $question_text, ':id' => $question_id];
    if ($image_path !== null) { $sql .= ", question_image = :img"; $params[':img'] = $image_path; }
    if ($file_path  !== null) { $sql .= ", question_file = :file"; $params[':file'] = $file_path; }
    $sql .= " WHERE question_id = :id AND delete_at IS NULL";
    $stmt = $pdo_connect->prepare($sql);
    $stmt->execute($params);
    $stmt->closeCursor();

    // แทนที่ตัวเลือก: soft delete เดิม แล้ว insert ใหม่
    $del = $pdo_connect->prepare("UPDATE tbl_question_choice SET delete_at = NOW() WHERE question_id = :qid AND delete_at IS NULL");
    $del->execute([':qid' => $question_id]);
    $del->closeCursor();

    $cstmt = $pdo_connect->prepare(
        "INSERT INTO tbl_question_choice (question_id, question_choice_text, question_choice_correct)
         VALUES (:qid, :text, :correct)"
    );
    foreach ($valid as $idx => $text) {
        $cstmt->execute([
            ':qid'     => $question_id,
            ':text'    => $text,
            ':correct' => ($idx === $correct) ? '1' : '0',
        ]);
    }
    $cstmt->closeCursor();

    $pdo_connect->commit();
    Response::json(1, 'บันทึกการแก้ไขคำถามสำเร็จ', ['question_id' => $question_id]);
} catch (Exception $e) {
    if ($pdo_connect->inTransaction()) { $pdo_connect->rollBack(); }
    error_log('Update Question Error: ' . $e->getMessage());
    Response::json(0, 'เกิดข้อผิดพลาดในการบันทึกข้อมูล', null);
} finally {
    $pdo_connect = null;
}
