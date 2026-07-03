<?php
// สร้างใบรับรองผลการสอบเป็นไฟล์ PDF จริง (mPDF) แล้วเปิดใน viewer ของเบราว์เซอร์
// ข้อมูลจาก tbl_course_enrollment + tbl_user + tbl_course

use App\Utility\Auth;
use App\Utility\Response;
use App\Utility\Pdf;
use App\Database\Connection;

// เปิดผ่าน form POST (ไม่มี header Authorization) -> รับ token จาก POST แทน
if (empty($_SERVER['HTTP_AUTHORIZATION']) && empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && !empty($_POST['access_token'])) {
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $_POST['access_token'];
}

$access_token = Auth::requireUserToken();
$admin_id = $access_token->user_id ?? null;
if (!$admin_id) {
    Response::json(0, 'Unauthorized', null);
}

$enroll_id = isset($_POST['enroll_id']) ? (int) $_POST['enroll_id'] : 0;
if ($enroll_id <= 0) {
    Response::json(0, 'ไม่พบรายการ', null);
}

$db_instance = new Connection();
$pdo_connect = $db_instance->getPdo();
if (!$pdo_connect) {
    Response::json(0, 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', null);
}

$stmt = $pdo_connect->prepare(
    "SELECT e.enroll_id, e.enroll_is_completed, e.create_at,
            u.user_firstname, u.user_lastname, u.user_citizen_id, u.user_cpd_no, u.id_card_image,
            c.course_name, c.course_instructor, c.course_code_cpd_1, c.course_code_cpa_1,
            c.course_approval_date_1, c.course_cpd_hour, c.course_cpd_ethics, c.course_cpd_other,
            c.course_number_exam,
            (SELECT a.attempt_score FROM tbl_exam_attempt a
              WHERE a.attempt_user_id = e.enroll_user_id AND a.attempt_course_id = e.enroll_course_id
              ORDER BY a.attempt_id DESC LIMIT 1) AS score,
            (SELECT a.create_at FROM tbl_exam_attempt a
              WHERE a.attempt_user_id = e.enroll_user_id AND a.attempt_course_id = e.enroll_course_id
              ORDER BY a.attempt_id DESC LIMIT 1) AS exam_completed_at
     FROM tbl_course_enrollment e
     LEFT JOIN tbl_user u   ON e.enroll_user_id = u.user_id
     LEFT JOIN tbl_course c ON e.enroll_course_id = c.course_id
     WHERE e.enroll_id = :id AND e.delete_at IS NULL LIMIT 1"
);
$stmt->execute([':id' => $enroll_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

if (!$row) {
    Response::json(0, 'ไม่พบรายการนี้ หรือถูกยกเลิกไปแล้ว', null);
}

// ---- เตรียมค่า ----
// วันที่ในเอกสาร (เลขที่/วันที่หัวเอกสาร/วันที่อบรม/ไตรมาสรหัสหลักสูตร) อิงจาก
// "วันที่สอบเสร็จ" (สร้าง attempt ล่าสุดที่ใช้ตัดสินผ่าน/ไม่ผ่าน) แทนวันที่สมัครเรียนหรือวันที่กดอนุมัติ
$esc = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$ts  = !empty($row['exam_completed_at']) ? strtotime($row['exam_completed_at'])
     : ($row['create_at'] ? strtotime($row['create_at']) : time());
$cert_no = date('ym', $ts) . str_pad((string) $row['enroll_id'], 4, '0', STR_PAD_LEFT);
$fullname = trim(($row['user_firstname'] ?? '') . ' ' . ($row['user_lastname'] ?? ''));
$acc = (string) ($row['user_citizen_id'] ?? ($row['user_cpd_no'] ?? ''));
$course = (string) ($row['course_name'] ?? '');
$code = (string) ($row['course_code_cpd_1'] ?? ($row['course_code_cpa_1'] ?? ''));
$approve_date = (!empty($row['course_approval_date_1']) && $row['course_approval_date_1'] !== '0000-00-00')
    ? date('d/m/Y', strtotime($row['course_approval_date_1'])) : '-';
$instructor = trim((string) ($row['course_instructor'] ?? '')) ?: '-';
$train_date = date('d/m/Y', $ts);

$cpd_hour   = number_format((float) ($row['course_cpd_hour'] ?? 0), 2);
$cpd_ethics = number_format((float) ($row['course_cpd_ethics'] ?? 0), 2);
$cpd_other  = (float) ($row['course_cpd_other'] ?? 0);
$hours = 'บัญชี ' . $cpd_hour . ' ชม. จรรยาบรรณ ' . $cpd_ethics . ' ชม.';
if ($cpd_other > 0) { $hours .= ' อื่น ๆ ' . number_format($cpd_other, 2) . ' ชม.'; }
$hours .= ' สำหรับผู้ทำบัญชี';

$total = (int) ($row['course_number_exam'] ?? 0);
$score = $row['score'] !== null ? (int) $row['score'] : null;
$score_txt = ($score !== null && $total > 0) ? number_format($score / $total * 100, 2) . ' %' : '-';

// รูปบัตรประชาชน (KYC) อยู่ในโปรเจกต์ลูกค้า (cpdth) = โฟลเดอร์พี่น้อง
$id_img_uri = '';
$id_card = trim((string) ($row['id_card_image'] ?? ''));
if ($id_card !== '') {
    $sibling = dirname(dirname(__DIR__, 3)) . '/cpdth/';
    $id_img_uri = Pdf::fileToDataUri($sibling . ltrim($id_card, '/'));
}

// ---- HTML สำหรับ PDF (mPDF: ใช้ table แทน flex) ----
$company = 'บริษัท เอ เอ็ม ซีพีดี จำกัด';
$tax_id  = '0105565002221';
$agency  = '06-330';

$id_img_html = $id_img_uri !== ''
    ? '<img src="' . $id_img_uri . '" style="width:175px;border:1px solid #bbb;">'
    : '';

// รหัสหลักสูตร: ไตรมาส (placeholder วงเล็บชุดสุดท้าย เช่น [01]) อิงจากวันที่ออก (train_date)
//   ม.ค.-มี.ค.=01, เม.ย.-มิ.ย.=02, ก.ค.-ก.ย.=03, ต.ค.-ธ.ค.=04 ; ส่วนปี [69] คงค่าเดิม
$quarter = str_pad((string) (int) ceil((int) date('n', $ts) / 3), 2, '0', STR_PAD_LEFT);
$code_q  = $code !== '' ? preg_replace('/\[\d{1,2}\](?=[^\[]*$)/', $quarter, $code, 1) : '';
$code_disp = $code_q !== '' ? str_replace(['[', ']'], '', $code_q) : '-';

// โลโก้ AM GROUP (ไฟล์อยู่ในโปรเจกต์เรา; เผื่อไว้ fallback ไป cpdth ถ้าไม่เจอ)
$logo_uri = Pdf::fileToDataUri(dirname(__DIR__, 3) . '/assets/images/am-group-logo.png');
if ($logo_uri === '') {
    $logo_uri = Pdf::fileToDataUri(dirname(dirname(__DIR__, 3)) . '/cpdth/assets/images/logo/am-group-logo.png');
}
$logo_html = $logo_uri !== '' ? '<img src="' . $logo_uri . '" style="width:115px;">' : 'AM GROUP';

// ลายเซ็นผู้มีอำนาจ — ถ้ามีไฟล์ assets/images/signature-amgroup.png จะแสดงให้, ไม่มีก็เว้นว่าง
$sign_uri  = Pdf::fileToDataUri(dirname(__DIR__, 3) . '/assets/images/signature-amgroup.png');
$sign_html = $sign_uri !== '' ? '<img src="' . $sign_uri . '" style="width:130px;height:auto;">' : '';

$html = '
<style>
    body { font-family: garuda; color:#222; font-size:12pt; }
    .hd { font-size:10.5pt; }
    .logo { font-size:16pt; font-weight:bold; color:#1d3557; letter-spacing:1px; }
    .company { font-weight:bold; font-size:12pt; }
    .title { text-align:center; font-size:14pt; font-weight:bold; }
    .lead { text-align:center; line-height:1.6; }
    .detail { font-size:11.5pt; }
    .detail td { vertical-align:top; line-height:1.5; }
    .lbl { white-space:nowrap; }
    .sign { text-align:center; line-height:1.6; }
</style>

<table width="100%" class="hd"><tr>
    <td width="34%" class="logo">' . $logo_html . '</td>
    <td width="33%" align="center" class="company">' . $esc($company) . '</td>
    <td width="33%" align="right">' . $esc($cert_no) . '</td>
</tr></table>
<table width="100%" class="hd" style="margin-top:6px;"><tr>
    <td width="50%">CPD e-Learning</td>
    <td width="50%" align="right">วันที่ ' . $esc($train_date) . '</td>
</tr></table>

<div class="title" style="margin:10px 0;">หนังสือรับรอง</div>

<p class="lead">ตามที่' . $esc($company) . ' เลขประจำตัวผู้เสียภาษี ' . $esc($tax_id) . ' รหัสหน่วยงาน ' . $esc($agency) . '
ได้จัดฝึกอบรมและสัมมนาหลักสูตร ที่ได้รับความเห็นชอบจากสภาวิชาชีพบัญชี
ในพระบรมราชูปถัมภ์ตามข้อบังคับกับสภาวิชาชีพ เพื่อพัฒนาความรู้ต่อเนื่องทางวิชาชีพของผู้ทำบัญชี มีรายละเอียดดังนี้</p>

<table align="center" cellpadding="3" class="detail">
    <tr><td align="right" class="lbl">ผู้เข้าสัมมนา</td><td>:&nbsp;' . $esc($fullname !== '' ? $fullname : '-') . '</td></tr>
    <tr><td align="right" class="lbl">เลขที่ผู้ทำบัญชี</td><td>:&nbsp;' . $esc($acc !== '' ? $acc : '-') . '</td></tr>
    <tr><td align="right" class="lbl">ชื่อหลักสูตร</td><td>:&nbsp;' . $esc($course !== '' ? $course : '-') . '</td></tr>
    <tr><td colspan="2" style="height:6px;"></td></tr>
    <tr><td align="right" class="lbl">รหัสหลักสูตร</td><td>:&nbsp;' . $esc($code_disp) . '</td></tr>
    <tr><td align="right" class="lbl">วันที่อนุมัติหลักสูตร</td><td>:&nbsp;' . $esc($approve_date) . '</td></tr>
    <tr><td align="right" class="lbl">นับชั่วโมงด้าน</td><td>:&nbsp;' . $esc($hours) . '</td></tr>
    <tr><td align="right" class="lbl">วิทยากรนำเสนอ</td><td>:&nbsp;' . $esc($instructor) . '</td></tr>
    <tr><td align="right" class="lbl">วัน เดือน ปี ที่อบรม</td><td>:&nbsp;' . $esc($train_date) . '</td></tr>
    <tr><td align="right" class="lbl">สถานที่</td><td>:&nbsp;การพัฒนาความรู้ต่อเนื่องผ่านระบบเครือข่ายอินเตอร์เน็ต (e-Learning)</td></tr>
    <tr><td align="right" class="lbl">คะแนนสอบ</td><td>:&nbsp;' . $esc($score_txt) . '</td></tr>
</table>

<br>
<table width="100%"><tr>
    <td width="50%" valign="bottom">' . $id_img_html . '</td>
    <td width="50%" valign="bottom" class="sign">
        บริษัทฯ ขอรับรองว่าท่านได้ผ่านการสัมมนาหลักสูตรดังกล่าวจริง<br>
        ' . ($sign_html !== '' ? $sign_html . '<br>' : '<br><br><br>') . '
        ขอแสดงความนับถือ<br>
        ในนาม ' . $esc($company) . '
    </td>
</tr></table>
';

try {
    $pdf = Pdf::make($html, [
        'title'         => 'CourseCertificate',
        'font_size'     => 12,
        'margin_left'   => 16,
        'margin_right'  => 16,
        'margin_top'    => 13,
        'margin_bottom' => 12,
    ]);
    $filename = 'certificate_' . $cert_no . '.pdf';

    // โหมดพรีวิว: ส่ง base64 ผ่าน JSON (เลี่ยง download manager จับไฟล์ PDF)
    if (($_POST['mode'] ?? '') === 'base64') {
        Response::json(1, 'ok', ['filename' => $filename, 'pdf' => base64_encode($pdf)]);
    }

    // โหมด stream ปกติ (เปิด/ดาวน์โหลดตรง)
    while (ob_get_level() > 0) { @ob_end_clean(); }
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdf));
    header('Cache-Control: private, max-age=0, must-revalidate');
    echo $pdf;
    exit;
} catch (\Throwable $e) {
    error_log('ExportCertificate Error: ' . $e->getMessage());
    Response::json(0, 'สร้าง PDF ไม่สำเร็จ: ' . $e->getMessage(), null);
}
