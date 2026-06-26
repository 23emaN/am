<?php
// เรียกหลังเบราว์เซอร์อัปไฟล์ขึ้น Vimeo (tus) เสร็จ -> ดึง embed URL (มี privacy hash) มาเก็บใน tbl_lesson

use App\Utility\Auth;
use App\Utility\Response;
use App\Database\Connection;
use Vimeo\Vimeo;

$access_token = Auth::requireUserToken();
$user_id = $access_token->user_id ?? null;
if (!$user_id) {
    Response::json(0, 'Unauthorized', null);
}

$lesson_id = isset($_POST['lesson_id']) ? (int) $_POST['lesson_id'] : 0;
$video_uri = isset($_POST['video_uri']) ? trim((string) $_POST['video_uri']) : '';
if ($lesson_id <= 0) {
    Response::json(0, 'ไม่พบรหัสบทเรียน', null);
}
if (!preg_match('#/videos/(\d+)#', $video_uri, $m)) {
    Response::json(0, 'รหัสวิดีโอไม่ถูกต้อง', null);
}
$video_id = $m[1];

$db_instance = new Connection();
$pdo_connect = $db_instance->getPdo();
if (!$pdo_connect) {
    Response::json(0, 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', null);
}

$token = trim($_ENV['VIMEO_ACCESS_TOKEN'] ?? '');

// embed URL ที่มี privacy hash (?h=...) — ดึงจาก API; ถ้าไม่ได้ค่อย fallback เป็น URL พื้นฐาน
$embed = 'https://player.vimeo.com/video/' . $video_id;
try {
    $lib  = new Vimeo($_ENV['VIMEO_CLIENT_ID'] ?? '', $_ENV['VIMEO_CLIENT_SECRET'] ?? '', $token);
    $info = $lib->request('/videos/' . $video_id, ['fields' => 'player_embed_url'], 'GET');
    if (!empty($info['body']['player_embed_url'])) {
        $embed = $info['body']['player_embed_url'];
    }
} catch (Exception $e) {
    error_log('Vimeo FinishUpload get embed url failed: ' . $e->getMessage());
}

try {
    $upd = $pdo_connect->prepare("UPDATE tbl_lesson SET lesson_video = :v WHERE lesson_id = :id AND delete_at IS NULL");
    $upd->execute([':v' => $embed, ':id' => $lesson_id]);
    $upd->closeCursor();

    Response::json(1, 'อัปโหลดวิดีโอขึ้น Vimeo สำเร็จ', ['lesson_video' => $embed, 'video_id' => $video_id]);
} catch (Exception $e) {
    error_log('FinishUpload DB Error: ' . $e->getMessage());
    Response::json(0, $e->getMessage(), null);
} finally {
    $pdo_connect = null;
}
