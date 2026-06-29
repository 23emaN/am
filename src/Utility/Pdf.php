<?php

namespace App\Utility;

use Mpdf\Mpdf;

/**
 * ตัวช่วยสร้าง PDF กลาง (mPDF) — ใช้ฟอนต์ไทย Garuda, A4
 * ใช้สำหรับเอกสารทางการ เช่น ใบรับรองผลการสอบ / ใบกำกับภาษี
 */
class Pdf
{
    /** สร้าง PDF แล้วคืนเป็น string (binary) */
    public static function make(string $html, array $opts = []): string
    {
        $mpdf = new Mpdf([
            'mode'              => 'utf-8',
            'format'            => $opts['format'] ?? 'A4',
            'orientation'       => $opts['orientation'] ?? 'P',
            'default_font'      => $opts['font'] ?? 'garuda',
            'default_font_size' => $opts['font_size'] ?? 13,
            'margin_left'       => $opts['margin_left']   ?? 15,
            'margin_right'      => $opts['margin_right']  ?? 15,
            'margin_top'        => $opts['margin_top']    ?? 15,
            'margin_bottom'     => $opts['margin_bottom'] ?? 15,
            'tempDir'           => sys_get_temp_dir(),
        ]);
        $mpdf->SetTitle($opts['title'] ?? 'Document');

        // ป้องกันไฟล์ด้วยรหัสผ่าน (ต้องกรอกตอนเปิด) — owner password สุ่มอัตโนมัติ
        if (!empty($opts['password'])) {
            $mpdf->SetProtection(['print', 'copy'], (string) $opts['password']);
        }

        $mpdf->WriteHTML($html);
        return $mpdf->Output('', 'S');
    }

    /**
     * ส่ง PDF ออกทาง HTTP
     * @param bool $inline true = เปิดใน viewer ของเบราว์เซอร์, false = บังคับดาวน์โหลด
     */
    public static function stream(string $html, string $filename, bool $inline = true, array $opts = []): void
    {
        if (!isset($opts['title'])) {
            $opts['title'] = pathinfo($filename, PATHINFO_FILENAME);
        }
        $pdf = self::make($html, $opts);

        // เคลียร์ output ที่อาจหลุดมาก่อนหน้า (กัน PDF เสีย)
        while (ob_get_level() > 0) { @ob_end_clean(); }

        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        echo $pdf;
        exit;
    }

    /** แปลงรูปไฟล์ในเครื่องเป็น data URI (ให้ mPDF ฝังรูปได้ชัวร์) */
    public static function fileToDataUri(string $absPath): string
    {
        if ($absPath === '' || !is_file($absPath)) { return ''; }
        $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
        $mime = $ext === 'png' ? 'image/png' : ($ext === 'gif' ? 'image/gif' : 'image/jpeg');
        $bin = @file_get_contents($absPath);
        if ($bin === false) { return ''; }
        return 'data:' . $mime . ';base64,' . base64_encode($bin);
    }
}
