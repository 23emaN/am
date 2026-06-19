<?php
// เปิดดูเอกสารประกอบ "แบบ inline" (ให้ browser แสดงในแท็บใหม่ ไม่บังคับดาวน์โหลด)
// public เหมือนไฟล์ใน upload/ ที่เสิร์ฟตรงอยู่แล้ว (รูปหน้าปกฯลฯ)
require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Database\Connection;

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) { http_response_code(404); exit('ไม่พบไฟล์'); }

$pdo = (new Connection())->getPdo();
$stmt = $pdo->prepare("SELECT lesson_file_name, lesson_file_type FROM tbl_lesson_file WHERE lesson_file_id = :id AND delete_at IS NULL LIMIT 1");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) { http_response_code(404); exit('ไม่พบไฟล์'); }

$matches = glob(dirname(__DIR__) . '/upload/lesson_file/' . $id . '.*');
if (!$matches) { http_response_code(404); exit('ไม่พบไฟล์บนเซิร์ฟเวอร์'); }
$path = $matches[0];
$ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));

// เสิร์ฟด้วย mime ตามนามสกุล (ให้ browser แสดง inline ได้ถูกต้อง)
$mimeMap = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp',
    'txt' => 'text/plain; charset=utf-8', 'csv' => 'text/csv; charset=utf-8',
];
$serveMime = $mimeMap[$ext] ?? ($row['lesson_file_type'] ?: 'application/octet-stream');

$name = trim((string)$row['lesson_file_name']);
if ($name === '') { $name = 'document'; }

header('Content-Type: ' . $serveMime);
// inline = เปิดดูในแท็บ (ไม่ใช่ attachment ที่บังคับโหลด); filename* รองรับชื่อไทย
header("Content-Disposition: inline; filename*=UTF-8''" . rawurlencode($name) . '.' . $ext);
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
readfile($path);
