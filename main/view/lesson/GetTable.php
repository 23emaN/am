<?php
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    $list_data = $data["list_data"] ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 fw-bold">บทเรียน</h4>
    <button type="button" class="btn btn-success" onclick="OpenAddLesson()">เพิ่มบทเรียนใหม่</button>
</div>

<div class="table-responsive">
    <table class="table align-middle w-100">
        <thead>
            <tr>
                <th scope="col" class="text-center" style="width: 80px;">บทที่</th>
                <th scope="col">ชื่อบทเรียน</th>
                <th scope="col" class="text-center" style="width: 140px;">สถานะวิดีโอ</th>
                <th scope="col" class="text-center" style="width: 140px;">สถานะคำถาม</th>
                <th scope="col" class="text-center" style="width: 160px;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($list_data) > 0): ?>
                <?php foreach ($list_data as $row): ?>
                    <?php
                        $lesson_id   = (int)($row['lesson_id'] ?? 0);
                        $order       = $row['lesson_order'] ?? '';
                        $has_video   = trim((string)($row['lesson_video'] ?? '')) !== '';
                        $has_question = (string)($row['lesson_question'] ?? '0') === '1';
                    ?>
                    <tr>
                        <td class="text-center"><?php echo htmlspecialchars((string)$order); ?></td>
                        <td><?php echo htmlspecialchars($row['lesson_name'] ?? ''); ?></td>
                        <td class="text-center">
                            <?php if ($has_video): ?>
                                <span class="badge bg-success">พร้อมใช้งาน</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">ยังไม่มี</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($has_question): ?>
                                <span class="badge bg-success">เปิดใช้งาน</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">ปิดใช้งาน</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-warning" onclick="GotoLessonManage(<?php echo $lesson_id; ?>)">จัดการ</button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="DeleteLesson(<?php echo $lesson_id; ?>)">ลบ</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีบทเรียน</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
