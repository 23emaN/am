<?php
// builder: รายงานสรุปใบรับรองที่ออกให้ผู้ทำบัญชี/ผู้สอบบัญชี (exam_result)
// คอลัมน์: ลำดับ | ชื่อ | นามสกุล | เบอร์โทร | อีเมล | เลขบัตรประชาชน | เลขผู้สอบ | คอร์ส | คะแนนสอบ
//          | ลำดับใบรับรองผู้ทำบัญชี | ลำดับใบรับรองผู้สอบบัญชี
//          | วันที่อนุมัติใบรับรองผู้ทำบัญชี | วันที่อนุมัติใบรับรองผู้สอบบัญชี | วันที่สอบผ่าน | วันที่ชำระเงิน
// ฐานข้อมูล: การลงทะเบียนที่ "เรียนจบแล้ว" (enroll_is_completed='1')
// ใช้ตัวแปรจาก dispatcher: $pdo, $norm_date, $fmt_date, $fmt_datetime, $cert_no, &$ss, $filename

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

$course_id = (int) ($_POST['course_id'] ?? 0);
$from = $norm_date((string) ($_POST['from'] ?? ''));
$to   = $norm_date((string) ($_POST['to'] ?? ''));

$where  = ["e.delete_at IS NULL", "e.enroll_is_completed = '1'"];
$params = [];
if ($course_id > 0) { $where[] = 'e.enroll_course_id = :course_id'; $params[':course_id'] = $course_id; }
if ($from !== '') { $where[] = 'DATE(COALESCE(e.enroll_completed_at, e.create_at)) >= :from'; $params[':from'] = $from; }
if ($to !== '')   { $where[] = 'DATE(COALESCE(e.enroll_completed_at, e.create_at)) <= :to';   $params[':to']   = $to; }
$where_sql = 'WHERE ' . implode(' AND ', $where);

$sql = "SELECT e.enroll_id, e.create_at, e.enroll_completed_at, e.enroll_user_id, e.enroll_course_id,
               u.user_firstname, u.user_lastname, u.user_phone, u.user_email,
               u.user_citizen_id, u.user_cpa_no, u.user_cpd_no,
               c.course_name,
               (SELECT a.attempt_score FROM tbl_exam_attempt a
                 WHERE a.attempt_user_id = e.enroll_user_id AND a.attempt_course_id = e.enroll_course_id
                 ORDER BY a.attempt_id DESC LIMIT 1) AS score,
               (SELECT a2.create_at FROM tbl_exam_attempt a2
                 WHERE a2.attempt_user_id = e.enroll_user_id AND a2.attempt_course_id = e.enroll_course_id
                   AND a2.attempt_pass = '1'
                 ORDER BY a2.attempt_id DESC LIMIT 1) AS pass_date,
               (SELECT MIN(o.created_at) FROM tbl_orders o
                  JOIN tbl_order_detail od ON od.order_id = o.order_id
                 WHERE o.user_id = e.enroll_user_id AND od.course_id = e.enroll_course_id
                   AND o.payment_status = '1') AS pay_date
        FROM tbl_course_enrollment e
        LEFT JOIN tbl_user u   ON e.enroll_user_id = u.user_id
        LEFT JOIN tbl_course c ON e.enroll_course_id = c.course_id
        $where_sql
        ORDER BY COALESCE(e.enroll_completed_at, e.create_at) DESC, e.enroll_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$sheet->setTitle('Worksheet');

$headers = ['ลำดับ', 'ชื่อ', 'นามสกุล', 'เบอร์โทร', 'อีเมล', 'เลขบัตรประชาชน', 'เลขผู้สอบ', 'คอร์ส', 'คะแนนสอบ',
            'ลำดับใบรับรองผู้ทำบัญชี', 'ลำดับใบรับรองผู้สอบบัญชี',
            'วันที่อนุมัติใบรับรองผู้ทำบัญชี', 'วันที่อนุมัติใบรับรองผู้สอบบัญชี', 'วันที่สอบผ่าน', 'วันที่ชำระเงิน'];
$sheet->fromArray($headers, null, 'A1');

$r = 2; $i = 1;
foreach ($rows as $row) {
    $has_cpd = trim((string) ($row['user_cpd_no'] ?? '')) !== '';
    $has_cpa = trim((string) ($row['user_cpa_no'] ?? '')) !== '';
    $running = $cert_no($row['create_at'], $row['enroll_id']);
    $approved = $fmt_date($row['enroll_completed_at'] ?: $row['create_at']);

    $sheet->setCellValue('A' . $r, $i++);
    $sheet->setCellValue('B' . $r, (string) ($row['user_firstname'] ?? ''));
    $sheet->setCellValue('C' . $r, (string) ($row['user_lastname'] ?? ''));
    $sheet->setCellValueExplicit('D' . $r, (string) ($row['user_phone'] ?? ''), DataType::TYPE_STRING);
    $sheet->setCellValue('E' . $r, (string) ($row['user_email'] ?? ''));
    $sheet->setCellValueExplicit('F' . $r, (string) ($row['user_citizen_id'] ?? ''), DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('G' . $r, (string) ($row['user_cpa_no'] ?? ''), DataType::TYPE_STRING);
    $sheet->setCellValue('H' . $r, (string) ($row['course_name'] ?? ''));
    $sheet->setCellValue('I' . $r, $row['score'] !== null ? (int) $row['score'] : '');
    $sheet->setCellValueExplicit('J' . $r, $has_cpd ? $running : '', DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('K' . $r, $has_cpa ? $running : '', DataType::TYPE_STRING);
    $sheet->setCellValue('L' . $r, $has_cpd ? $approved : '');
    $sheet->setCellValue('M' . $r, $has_cpa ? $approved : '');
    $sheet->setCellValue('N' . $r, $fmt_datetime($row['pass_date']));
    $sheet->setCellValue('O' . $r, $fmt_datetime($row['pay_date']));
    $r++;
}

$sheet->getStyle('A1:O1')->getFont()->setBold(true);
foreach (range('A', 'O') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }

$filename = 'exam_result_' . date('Ymd_His') . '.xlsx';
