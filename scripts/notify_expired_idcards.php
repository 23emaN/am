<?php
/**
 * notify_expired_idcards.php — แจ้งเตือนลูกค้าทางอีเมลเมื่อบัตรประชาชนหมดอายุ (รันด้วย cron รายวัน)
 *
 * ตรรกะ: หา user ที่บัตรหมดอายุ (id_card_expiry_date < วันนี้) มีอีเมล และยังไม่เคยแจ้งเตือนสำหรับ
 *   วันหมดอายุนั้น (id_card_expiry_notified <> id_card_expiry_date) -> ส่งเมล + บันทึกว่าแจ้งแล้ว
 *   (ต่อบัตรใหม่แล้วหมดอายุอีกครั้งจะแจ้งใหม่ เพราะวันหมดอายุเปลี่ยน)
 *
 * รันจาก command line เท่านั้น (ต้องมี .env + vendor + คอลัมน์ id_card_expiry_notified):
 *   php scripts/notify_expired_idcards.php               # dry-run: รายงานอย่างเดียว (ค่าเริ่มต้น กันส่งพลาด)
 *   php scripts/notify_expired_idcards.php --send        # ส่งเมลจริง + บันทึกว่าแจ้งแล้ว
 *   php scripts/notify_expired_idcards.php --send --limit=50   # จำกัดจำนวนต่อรอบ (กัน SMTP โดน throttle)
 *
 * ตั้ง cron (ตัวอย่าง: รายวัน 08:00 น.):
 *   0 8 * * * /usr/bin/php /path/to/backoffice/scripts/notify_expired_idcards.php --send >> /path/to/notify.log 2>&1
 */

use App\Database\Connection;
use App\Utility\Email;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

/* ---------- อ่าน argument ---------- */
$argvAll = $argv ?? [];
$do_send = in_array('--send', $argvAll, true); // ไม่ใส่ = dry-run (รายงานอย่างเดียว)
$limit   = 0;
foreach ($argvAll as $a) {
    if (strpos($a, '--limit=') === 0) {
        $limit = max(0, (int) substr($a, strlen('--limit=')));
    }
}

$log = function (string $m) { fwrite(STDOUT, $m . "\n"); };

/* ---------- ต่อ DB (Connection โหลด .env เข้า $_ENV ให้ Email::send ใช้ด้วย) ---------- */
try {
    $pdo = (new Connection())->getPdo();
} catch (\Throwable $e) {
    fwrite(STDERR, 'DB connect failed: ' . $e->getMessage() . "\n");
    exit(1);
}
if (!$pdo) {
    fwrite(STDERR, "DB connect failed.\n");
    exit(1);
}

/* ---------- หา user ที่บัตรหมดอายุและยังไม่ได้แจ้งสำหรับวันหมดอายุนี้ ---------- */
$sql = "SELECT user_id, user_firstname, user_lastname, user_email, id_card_expiry_date
        FROM tbl_user
        WHERE delete_at IS NULL
          AND user_email IS NOT NULL AND user_email <> ''
          AND id_card_expiry_date IS NOT NULL
          AND id_card_expiry_date <> '0000-00-00'
          AND id_card_expiry_date < CURDATE()
          AND (id_card_expiry_notified IS NULL OR id_card_expiry_notified <> id_card_expiry_date)
        ORDER BY id_card_expiry_date ASC";
if ($limit > 0) {
    $sql .= " LIMIT " . $limit;
}

try {
    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {
    fwrite(STDERR, 'Query failed (คอลัมน์ id_card_expiry_notified ยังไม่ถูกสร้าง? รัน migration ก่อน): ' . $e->getMessage() . "\n");
    exit(1);
}

$total = count($rows);
$log('[' . date('Y-m-d H:i:s') . '] พบผู้ใช้ที่บัตรหมดอายุและยังไม่ได้แจ้ง: ' . $total . ' ราย'
    . ($do_send ? '' : '   (DRY-RUN: ยังไม่ส่งจริง — ใส่ --send เพื่อส่ง)'));
if ($total === 0) {
    exit(0);
}

$upd = $pdo->prepare("UPDATE tbl_user SET id_card_expiry_notified = :exp WHERE user_id = :id AND delete_at IS NULL");
$esc = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$sent = 0;
$failed = 0;
foreach ($rows as $r) {
    $email  = trim((string) ($r['user_email'] ?? ''));
    $name   = trim(($r['user_firstname'] ?? '') . ' ' . ($r['user_lastname'] ?? ''));
    if ($name === '') { $name = 'ลูกค้า'; }
    $exp    = (string) $r['id_card_expiry_date'];
    $exp_th = date('d/m/Y', strtotime($exp));

    // dry-run: แค่ลิสต์ ไม่ส่ง ไม่บันทึก
    if (!$do_send) {
        $log(sprintf('  - #%s  %s  <%s>  หมดอายุ %s', $r['user_id'], $name, $email, $exp_th));
        continue;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $log('  x ข้าม #' . $r['user_id'] . ' (อีเมลไม่ถูกต้อง: ' . $email . ')');
        $failed++;
        continue;
    }

    $subject = 'แจ้งเตือน: บัตรประชาชนของท่านหมดอายุ - CPDTH';
    $body = '
<div style="font-family:Tahoma,Arial,sans-serif;font-size:14px;color:#222;max-width:600px;margin:auto;">
  <h2 style="color:#c0392b;">แจ้งเตือนบัตรประชาชนหมดอายุ</h2>
  <p>เรียน คุณ' . $esc($name) . '</p>
  <p>ระบบตรวจพบว่า <b>บัตรประชาชนที่ท่านใช้ยืนยันตัวตนกับ CPDTH หมดอายุแล้ว</b>
     (วันหมดอายุ: <b>' . $esc($exp_th) . '</b>)</p>
  <p>เพื่อให้ท่านใช้งานระบบและออกเอกสาร/ใบรับรองได้อย่างต่อเนื่อง
     กรุณาอัปเดตข้อมูลบัตรประชาชนใบใหม่ และยืนยันตัวตนอีกครั้งผ่านระบบ</p>
  <p style="margin-top:16px;">หากท่านได้ต่ออายุ/อัปเดตข้อมูลเรียบร้อยแล้ว สามารถละเว้นอีเมลฉบับนี้ได้</p>
  <p style="color:#888;font-size:12px;margin-top:20px;">อีเมลฉบับนี้ส่งจากระบบ CPDTH โดยอัตโนมัติ กรุณาอย่าตอบกลับ</p>
</div>';

    $ok = Email::send($email, $subject, $body, true);
    if ($ok) {
        // บันทึกว่าแจ้งสำหรับวันหมดอายุนี้แล้ว (กันส่งซ้ำรอบถัดไป)
        $upd->execute([':exp' => $exp, ':id' => (int) $r['user_id']]);
        $sent++;
        $log('  ✓ ส่งแล้ว #' . $r['user_id'] . '  <' . $email . '>');
    } else {
        $failed++;
        $log('  x ส่งไม่สำเร็จ #' . $r['user_id'] . '  <' . $email . '>  (ตรวจ SMTP ใน .env)');
    }
}

$log('[' . date('Y-m-d H:i:s') . '] สรุป: ส่งสำเร็จ ' . $sent . ' / ล้มเหลว ' . $failed . ' / ทั้งหมด ' . $total);
exit(0);
