<?php
    $course_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $breadcrumbs = [
        ['label' => 'คอร์สเรียน', 'url' => 'course'],
        ['label' => 'แก้ไขคอร์สเรียน #' . $course_id],
    ];
?>
<?php include "header.php"; ?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <?php include "navbar.php"; ?>

        <div class="px-2">
            <div class="card app-card bg-white border-0 rounded-3 mb-3">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">ดู/แก้ไขคอร์สเรียน</h2>
                    <button type="button" class="btn btn-danger BtnDeleteCourse" onclick="DeleteCourse()">ลบคอร์สเรียน</button>
                </div>

                <div class="card-body p-4 pt-0">
                    <ul class="nav nav-tabs mb-3" id="courseTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-general" type="button" role="tab">ทั่วไป</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-lesson" type="button" role="tab">บทเรียน</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-doc" type="button" role="tab">เอกสารประกอบบทเรียน</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-exam" type="button" role="tab">ข้อสอบ</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                            <div id="GetFormEdit"></div>
                        </div>
                        <div class="tab-pane fade" id="tab-lesson" role="tabpanel">
                            <div id="GetLessonTab"></div>
                        </div>
                        <div class="tab-pane fade" id="tab-doc" role="tabpanel">
                            <div id="GetDocTab"></div>
                        </div>
                        <div class="tab-pane fade" id="tab-exam" role="tabpanel">
                            <div id="GetExamTab"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include "footer.php"; ?>

    </div>
</div>

<!-- ===== Modal: เพิ่มบทเรียนใหม่ ===== -->
<div class="modal fade" id="modalAddLesson" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มบทเรียนใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAddLesson">
                    <div class="mb-3">
                        <label class="form-label fw-medium">บทเรียนที่ <span class="text-danger">*</span></label>
                        <input type="number" min="0" class="form-control" name="lesson_order" placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">ชื่อบทเรียน <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="lesson_name" placeholder="กรอกชื่อบทเรียน">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">คำถามระหว่างรับชม <span class="text-danger">*</span></label>
                        <select class="form-select" name="lesson_question">
                            <option value="0">ปิดใช้งานคำถาม</option>
                            <option value="1">เปิดใช้งานคำถาม</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">จำนวนคำถามระหว่างรับชม</label>
                        <input type="number" min="0" class="form-control" name="lesson_question_limit" placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">ระยะเวลาทำคำถามระหว่างรับชม (วินาที)</label>
                        <input type="number" min="0" class="form-control" name="lesson_question_time" placeholder="0">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-medium">รายละเอียดโดยย่อ</label>
                        <textarea class="form-control" name="lesson_overview" rows="3"></textarea>
                    </div>
                    <div class="alert alert-success small mb-0">
                        เมื่อเพิ่มบทเรียนแล้วจะถูกบันทึกทันที จึงสามารถเพิ่มข้อสอบ / วิดีโอ และเนื้อหาอื่น ๆ ของบทเรียนได้
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100 BtnAddLesson" onclick="SubmitAddLesson()">เพิ่มบทเรียน</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== Modal: เพิ่มเอกสารประกอบ ===== -->
<div class="modal fade" id="modalLessonFile" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มเอกสารประกอบการเรียน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formLessonFile" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-medium" for="lf_lesson_id">บทเรียน <span class="text-danger">*</span></label>
                        <select class="form-select" name="lesson_id" id="lf_lesson_id">
                            <option value="">กรุณาเลือกบทเรียน</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">ชื่อเอกสาร <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="lesson_file_name" placeholder="กรอกชื่อเอกสาร">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-medium">ไฟล์ <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="lesson_file">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100 BtnAddLessonFile" onclick="SubmitAddLessonFile()">เพิ่มเอกสารใหม่</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== Modal: เพิ่ม/แก้ไขข้อสอบ ===== -->
<div class="modal fade" id="modalExam" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalExamTitle">สร้างคำถามใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formExam" enctype="multipart/form-data">
                    <input type="hidden" name="exam_id" id="e_id">
                    <input type="hidden" name="exam_text" id="e_text">
                    <div class="mb-3">
                        <label class="form-label fw-medium">คำถาม <span class="text-danger">*</span></label>
                        <div id="editor_exam_text"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">ภาพ</label>
                        <input type="file" class="form-control" name="exam_image" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">ไฟล์</label>
                        <input type="file" class="form-control" name="exam_file">
                    </div>
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <label class="form-label fw-medium mb-0">ตัวเลือก <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-sm btn-success" onclick="AddExamChoiceRow('')">เพิ่มตัวเลือก</button>
                    </div>
                    <div id="examChoiceList"></div>
                    <div class="mb-2">
                        <label class="form-label fw-medium" for="examCorrectSelect">คำตอบที่ถูกต้อง <span class="text-danger">*</span></label>
                        <select class="form-select" id="examCorrectSelect"></select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success w-100 BtnSaveExam" onclick="SubmitExam()">บันทึกคำถาม</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== Modal: อัพโหลด Excel (ข้อสอบ) ===== -->
<div class="modal fade" id="modalUploadExam" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">อัพโหลดข้อมูลด้วยไฟล์ Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning small">
                    กรุณาอัพโหลดไฟล์ข้อสอบตามรูปแบบที่กำหนด โดยสามารถดาวน์โหลดตัวอย่างรูปแบบไฟล์ได้
                    <a href="sample/example_question.xlsx" download>ที่นี่</a>
                </div>
                <form id="formUploadExam" enctype="multipart/form-data">
                    <label class="form-label fw-medium">ไฟล์ข้อสอบ</label>
                    <input type="file" class="form-control" name="excel_file" accept=".xlsx,.xls">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100 BtnUploadExam" onclick="SubmitUploadExam()">อัพโหลด</button>
            </div>
        </div>
    </div>
</div>

<style>
    #editor_exam_text { height: 200px; background:#fff; }
    .ql-toolbar.ql-snow, .ql-container.ql-snow { border-color: #ced4da; }
    td.dt-control { cursor: pointer; }
    .exam-toggle { display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:50%; color:#fff; font-weight:bold; line-height:1; }
    .exam-toggle.plus { background:#28a745; }
    .exam-toggle.minus { background:#dc3545; }
</style>

<?php include "script.php"; ?>

</body>

</html>

<script>
    var COURSE_ID = <?php echo $course_id; ?>;
    var quillExam = null;

    $(document).ready(function () {
        if (!COURSE_ID) {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">ไม่พบรหัสคอร์สเรียน</span>', icon: "error", showConfirmButton: true })
                .then(() => { window.location.href = "course.php"; });
            return;
        }
        LoadGeneralTab();

        // โหลดแท็บแบบ lazy ครั้งแรกที่เปิด
        var lessonLoaded = false, examLoaded = false;
        $('button[data-bs-target="#tab-lesson"]').on('shown.bs.tab', function () {
            if (!lessonLoaded && typeof LoadLessonTab === 'function') { lessonLoaded = true; LoadLessonTab(); }
        });
        $('button[data-bs-target="#tab-exam"]').on('shown.bs.tab', function () {
            if (!examLoaded && typeof LoadExamTab === 'function') { examLoaded = true; LoadExamTab(); }
        });
        var docLoaded = false;
        $('button[data-bs-target="#tab-doc"]').on('shown.bs.tab', function () {
            if (!docLoaded && typeof LoadDocTab === 'function') { docLoaded = true; LoadDocTab(); }
        });
    });

    // ===== แท็บทั่วไป: ดึงข้อมูลคอร์ส + ตัวเลือก แล้ว render ฟอร์ม (dual-mode = edit) =====
    function LoadGeneralTab() {
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#GetFormEdit"); },
            type: "POST",
            url: "core.php",
            data: { request_state: "list_course", request_function: "get_course", course_id: COURSE_ID },
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    RenderEditForm(response.data);
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: true })
                        .then(() => { window.location.href = "course.php"; });
                }
            },
            complete: function () { HideLoadingOverlay("#GetFormEdit"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    function RenderEditForm(data) {
        const payload = {
            groups: data.groups || [],
            types: data.types || [],
            course: data.course || null,
        };
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#GetFormEdit"); },
            type: "POST",
            url: "view/listCourse/FormAddCourse.php",
            data: JSON.stringify(payload),
            contentType: "application/json; charset=utf-8",
            processData: false,
            dataType: "html",
            success: function (response) { $("#GetFormEdit").html(response); },
            complete: function () { HideLoadingOverlay("#GetFormEdit"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    // ===== ลบคอร์ส (soft delete) =====
    function DeleteCourse() {
        Swal.fire({
            title: "ยืนยันการลบ",
            html: '<span class="fw-bold text-danger">ต้องการลบคอร์สเรียนนี้ใช่หรือไม่?</span>',
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "ลบ",
            cancelButtonText: "ยกเลิก",
            confirmButtonColor: "#dc3545"
        }).then((res) => {
            if (!res.isConfirmed) { return; }
            $.ajax({
                type: "POST",
                url: "core.php",
                data: { request_state: "list_course", request_function: "delete_course", course_id: COURSE_ID },
                dataType: "json",
                success: function (response) {
                    if (response.result == 1) {
                        Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + response.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1500, timerProgressBar: true })
                            .then(() => { window.location.href = "course.php"; });
                    } else {
                        Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: false, timer: 2500, timerProgressBar: true });
                    }
                },
                error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
            });
        });
    }

    // ===== แท็บบทเรียน =====
    function LoadLessonTab() {
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#GetLessonTab"); },
            type: "POST",
            url: "core.php",
            data: { request_state: "lesson", request_function: "get_list_lesson", course_id: COURSE_ID },
            dataType: "json",
            success: function (response) {
                if (response.result == 1) { RenderLessonTable(response.data.list_data); }
                else { Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: false, timer: 2500 }); }
            },
            complete: function () { HideLoadingOverlay("#GetLessonTab"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
    function RenderLessonTable(list_data) {
        $.ajax({
            type: "POST",
            url: "view/lesson/GetTable.php",
            data: JSON.stringify({ list_data: list_data }),
            contentType: "application/json; charset=utf-8",
            processData: false,
            dataType: "html",
            success: function (html) { $("#GetLessonTab").html(html); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
    function OpenAddLesson() {
        $('#formAddLesson')[0].reset();
        new bootstrap.Modal(document.getElementById('modalAddLesson')).show();
    }
    function SubmitAddLesson() {
        const name = $('#formAddLesson [name="lesson_name"]').val().trim();
        if (name === "") {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณากรอกชื่อบทเรียน</span>', icon: "warning", showConfirmButton: false, timer: 2000 });
            return;
        }
        const data = $('#formAddLesson').serializeArray();
        data.push({ name: "request_state", value: "lesson" });
        data.push({ name: "request_function", value: "add_lesson" });
        data.push({ name: "course_id", value: COURSE_ID });
        $.ajax({
            beforeSend: function () { ShowLoadingButton('.BtnAddLesson'); },
            type: "POST", url: "core.php", data: $.param(data), dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    bootstrap.Modal.getInstance(document.getElementById('modalAddLesson')).hide();
                    Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + response.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1500 });
                    LoadLessonTab();
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: false, timer: 2500 });
                }
            },
            complete: function () { HideLoadingButton('.BtnAddLesson'); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
    function DeleteLesson(lesson_id) {
        Swal.fire({ title: "ยืนยันการลบ", html: '<span class="fw-bold text-danger">ต้องการลบบทเรียนนี้ใช่หรือไม่?</span>', icon: "warning", showCancelButton: true, confirmButtonText: "ลบ", cancelButtonText: "ยกเลิก", confirmButtonColor: "#dc3545" })
        .then((res) => {
            if (!res.isConfirmed) { return; }
            $.ajax({
                type: "POST", url: "core.php",
                data: { request_state: "lesson", request_function: "delete_lesson", lesson_id: lesson_id },
                dataType: "json",
                success: function (response) {
                    if (response.result == 1) {
                        Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + response.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1500 });
                        LoadLessonTab();
                    } else {
                        Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: false, timer: 2500 });
                    }
                },
                error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
            });
        });
    }
    function GotoLessonManage(lesson_id) {
        window.location.href = "lesson_manage.php?course_id=" + COURSE_ID + "&lesson_id=" + lesson_id;
    }

    // ===== แท็บเอกสารประกอบบทเรียน =====
    function LoadDocTab() {
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#GetDocTab"); },
            type: "POST", url: "core.php",
            data: { request_state: "lesson_file", request_function: "get_list_lesson_file", course_id: COURSE_ID },
            dataType: "json",
            success: function (response) {
                if (response.result == 1) { RenderDocTable(response.data.list_data); }
                else { ToastResult(response); }
            },
            complete: function () { HideLoadingOverlay("#GetDocTab"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
    function RenderDocTable(list_data) {
        $.ajax({
            type: "POST", url: "view/lessonFile/GetTable.php",
            data: JSON.stringify({ list_data: list_data }),
            contentType: "application/json; charset=utf-8", processData: false, dataType: "html",
            success: function (html) { $("#GetDocTab").html(html); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
    function OpenAddLessonFile() {
        $('#formLessonFile')[0].reset();
        // โหลดบทเรียนของคอร์สลง dropdown
        $.ajax({
            type: "POST", url: "core.php",
            data: { request_state: "lesson", request_function: "get_list_lesson", course_id: COURSE_ID },
            dataType: "json",
            success: function (response) {
                var opts = '<option value="">กรุณาเลือกบทเรียน</option>';
                if (response.result == 1) {
                    (response.data.list_data || []).forEach(function (l) {
                        opts += '<option value="' + l.lesson_id + '">' + EscapeHTML(l.lesson_name || ('บทเรียน #' + l.lesson_id)) + '</option>';
                    });
                }
                $('#lf_lesson_id').html(opts);
                new bootstrap.Modal(document.getElementById('modalLessonFile')).show();
            },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
    function SubmitAddLessonFile() {
        if (!$('#lf_lesson_id').val()) {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณาเลือกบทเรียน</span>', icon: "warning", showConfirmButton: false, timer: 2000 });
            return;
        }
        if ($('#formLessonFile [name="lesson_file_name"]').val().trim() === "") {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณากรอกชื่อเอกสาร</span>', icon: "warning", showConfirmButton: false, timer: 2000 });
            return;
        }
        if (!$('#formLessonFile [name="lesson_file"]').val()) {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณาเลือกไฟล์</span>', icon: "warning", showConfirmButton: false, timer: 2000 });
            return;
        }
        var fd = new FormData($('#formLessonFile')[0]);
        fd.append('request_state', 'lesson_file');
        fd.append('request_function', 'add_lesson_file');
        $.ajax({
            beforeSend: function () { ShowLoadingButton('.BtnAddLessonFile'); },
            type: "POST", url: "core.php", data: fd, processData: false, contentType: false, dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    bootstrap.Modal.getInstance(document.getElementById('modalLessonFile')).hide();
                    ToastResult(response);
                    LoadDocTab();
                } else { ToastResult(response); }
            },
            complete: function () { HideLoadingButton('.BtnAddLessonFile'); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
    function DeleteLessonFile(file_id) {
        Swal.fire({ title: "ยืนยันการลบ", html: '<span class="fw-bold text-danger">ต้องการลบเอกสารนี้ใช่หรือไม่?</span>', icon: "warning", showCancelButton: true, confirmButtonText: "ลบ", cancelButtonText: "ยกเลิก", confirmButtonColor: "#dc3545" })
        .then((res) => {
            if (!res.isConfirmed) { return; }
            $.ajax({
                type: "POST", url: "core.php",
                data: { request_state: "lesson_file", request_function: "delete_lesson_file", lesson_file_id: file_id },
                dataType: "json",
                success: function (response) { ToastResult(response); if (response.result == 1) { LoadDocTab(); } },
                error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
            });
        });
    }

    // ===== แท็บข้อสอบ =====
    function LoadExamTab() {
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#GetExamTab"); },
            type: "POST", url: "core.php",
            data: { request_state: "exam", request_function: "get_list_exam", course_id: COURSE_ID },
            dataType: "json",
            success: function (response) {
                if (response.result == 1) { RenderExamTable(response.data.list_data); }
                else { ToastResult(response); }
            },
            complete: function () { HideLoadingOverlay("#GetExamTab"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
    function StripTags(h) { var t = document.createElement('div'); t.innerHTML = h || ''; return (t.textContent || t.innerText || '').trim(); }
    function ExamChildRow(d) {
        var hasFile = (d.exam_image || d.exam_file) ? 'มี' : 'ไม่มีข้อมูล';
        var correct = d.correct_text ? EscapeHTML(d.correct_text) : '-';
        return '<table class="table table-borderless mb-0">' +
            '<tr><td style="width:160px" class="fw-bold">คำถาม</td><td>' + EscapeHTML(StripTags(d.exam_text)) + '</td></tr>' +
            '<tr><td class="fw-bold">ไฟล์/ภาพ</td><td>' + hasFile + '</td></tr>' +
            '<tr><td class="fw-bold">คำตอบที่ถูกต้อง</td><td>' + correct + '</td></tr>' +
            '<tr><td class="fw-bold">Action</td><td>' +
                '<button type="button" class="btn btn-sm btn-warning" onclick="OpenEditExam(' + d.exam_id + ')">แก้ไข</button> ' +
                '<button type="button" class="btn btn-sm btn-danger" onclick="DeleteExam(' + d.exam_id + ')">ลบ</button>' +
            '</td></tr></table>';
    }
    function RenderExamTable(list_data) {
        list_data = list_data || [];
        var head = '<div class="d-flex justify-content-between align-items-center mb-3">' +
            '<h4 class="mb-0 fw-bold">ข้อสอบ</h4>' +
            '<div class="d-flex gap-2">' +
            '<button type="button" class="btn btn-info" onclick="OpenUploadExam()">อัพโหลดคำถาม</button>' +
            '<button type="button" class="btn btn-success" onclick="OpenAddExam()">เพิ่มคำถาม</button>' +
            '</div></div>';

        var rows = '';
        list_data.forEach(function (d, i) {
            var no = i + 1;
            // แถวหลัก: ปุ่มวงกลม +/− + ลำดับ
            rows += '<tr class="exam-main" data-exam-idx="' + i + '">' +
                '<td class="exam-control text-center" style="cursor:pointer"><span class="exam-toggle plus">+</span></td>' +
                '<td class="text-start">' + no + '</td>' +
                '<td></td>' +
                '</tr>';
            // แถวรายละเอียด (ซ่อนไว้ก่อน) กางด้วยปุ่ม +/−
            rows += '<tr class="exam-detail" data-exam-idx="' + i + '" style="display:none">' +
                '<td></td><td colspan="2">' + ExamChildRow(d) + '</td>' +
                '</tr>';
        });

        $("#GetExamTab").html(head +
            '<table id="ExamTable" class="table align-middle w-100" style="width:100%"><thead><tr>' +
            '<th style="width:50px"></th><th style="width:100px">ลำดับ</th><th></th>' +
            '</tr></thead><tbody>' + rows + '</tbody></table>');
    }

    // กดวงกลม +/− เพื่อกาง/ยุบรายละเอียดของแต่ละข้อ (event delegation, ไม่ใช้ DataTables)
    $('#GetExamTab').off('click', 'td.exam-control').on('click', 'td.exam-control', function () {
        var $mainTr = $(this).closest('tr.exam-main');
        var idx = $mainTr.data('exam-idx');
        var $detail = $mainTr.siblings('tr.exam-detail[data-exam-idx="' + idx + '"]');
        var $glyph = $(this).find('.exam-toggle');
        if ($detail.is(':visible')) {
            $detail.hide();
            $glyph.removeClass('minus').addClass('plus').text('+');
        } else {
            $detail.show();
            $glyph.removeClass('plus').addClass('minus').text('−');
        }
    });
    function EnsureExamQuill() {
        if (!quillExam && typeof Quill !== 'undefined') {
            quillExam = new Quill('#editor_exam_text', {
                theme: 'snow',
                modules: { toolbar: [['bold', 'italic', 'underline'], [{ 'list': 'ordered' }, { 'list': 'bullet' }], ['link'], ['clean']] }
            });
        }
    }
    function AddExamChoiceRow(text) {
        var row = $('<div class="input-group mb-2 exam-choice-row">' +
            '<span class="input-group-text">ข้อ</span>' +
            '<textarea class="form-control" name="choice_text[]" rows="1"></textarea>' +
            '<button type="button" class="btn btn-outline-danger" onclick="RemoveExamChoiceRow(this)">ลบ</button>' +
            '</div>');
        row.find('textarea').val(text || '');
        $('#examChoiceList').append(row);
        ReindexExamChoices();
        RefreshExamCorrectOptions();
    }
    function RemoveExamChoiceRow(btn) {
        $(btn).closest('.exam-choice-row').remove();
        ReindexExamChoices();
        RefreshExamCorrectOptions();
    }
    function ReindexExamChoices() {
        $('#examChoiceList .exam-choice-row').each(function (i) {
            $(this).find('.input-group-text').text('ข้อ ' + (i + 1));
        });
    }
    function RefreshExamCorrectOptions() {
        var prev = $('#examCorrectSelect').val();
        var n = $('#examChoiceList .exam-choice-row').length;
        var html = '<option value="">---กรุณาเลือกคำตอบที่ถูก---</option>';
        for (var i = 1; i <= n; i++) { html += '<option value="' + i + '">ตัวเลือกที่ ' + i + '</option>'; }
        $('#examCorrectSelect').html(html);
        if (prev && prev <= n) { $('#examCorrectSelect').val(prev); }
    }
    function OpenAddExam() {
        $('#modalExamTitle').text('สร้างคำถามใหม่');
        $('#formExam')[0].reset();
        $('#e_id').val('');
        EnsureExamQuill();
        if (quillExam) { quillExam.setText(''); }
        $('#examChoiceList').empty();
        AddExamChoiceRow(''); AddExamChoiceRow('');
        new bootstrap.Modal(document.getElementById('modalExam')).show();
    }
    function OpenEditExam(eid) {
        $.ajax({
            type: "POST", url: "core.php",
            data: { request_state: "exam", request_function: "get_exam", exam_id: eid },
            dataType: "json",
            success: function (response) {
                if (response.result != 1) { ToastResult(response); return; }
                $('#modalExamTitle').text('แก้ไขคำถาม');
                $('#formExam')[0].reset();
                $('#e_id').val(eid);
                EnsureExamQuill();
                if (quillExam) { quillExam.root.innerHTML = response.data.exam.exam_text || ''; }
                $('#examChoiceList').empty();
                var correctIdx = 0;
                response.data.choices.forEach(function (c, i) {
                    AddExamChoiceRow(c.exam_choice_text || '');
                    if (String(c.exam_choice_correct) === '1') { correctIdx = i + 1; }
                });
                if (correctIdx > 0) { $('#examCorrectSelect').val(correctIdx); }
                new bootstrap.Modal(document.getElementById('modalExam')).show();
            },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
    function SubmitExam() {
        if (quillExam) {
            var html = quillExam.root.innerHTML;
            if (quillExam.getText().trim() === '') { html = ''; }
            $('#e_text').val(html);
        }
        if ($('#e_text').val().trim() === '') {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณากรอกคำถาม</span>', icon: "warning", showConfirmButton: false, timer: 2000 });
            return;
        }
        if (!$('#examCorrectSelect').val()) {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณาเลือกคำตอบที่ถูกต้อง</span>', icon: "warning", showConfirmButton: false, timer: 2000 });
            return;
        }
        var isEdit = $('#e_id').val() !== '';
        var fd = new FormData($('#formExam')[0]);
        fd.append('correct', $('#examCorrectSelect').val());
        fd.append('course_id', COURSE_ID);
        fd.append('request_state', 'exam');
        fd.append('request_function', isEdit ? 'update_exam' : 'add_exam');
        $.ajax({
            beforeSend: function () { ShowLoadingButton('.BtnSaveExam'); },
            type: "POST", url: "core.php", data: fd, processData: false, contentType: false, dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    bootstrap.Modal.getInstance(document.getElementById('modalExam')).hide();
                    ToastResult(response);
                    LoadExamTab();
                } else { ToastResult(response); }
            },
            complete: function () { HideLoadingButton('.BtnSaveExam'); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
    function DeleteExam(eid) {
        Swal.fire({ title: "ยืนยันการลบ", html: '<span class="fw-bold text-danger">ต้องการลบข้อสอบนี้ใช่หรือไม่?</span>', icon: "warning", showCancelButton: true, confirmButtonText: "ลบ", cancelButtonText: "ยกเลิก", confirmButtonColor: "#dc3545" })
        .then((res) => {
            if (!res.isConfirmed) { return; }
            $.ajax({
                type: "POST", url: "core.php",
                data: { request_state: "exam", request_function: "delete_exam", exam_id: eid },
                dataType: "json",
                success: function (response) { ToastResult(response); if (response.result == 1) { LoadExamTab(); } },
                error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
            });
        });
    }
    function OpenUploadExam() {
        $('#formUploadExam')[0].reset();
        new bootstrap.Modal(document.getElementById('modalUploadExam')).show();
    }
    function SubmitUploadExam() {
        if (!$('#formUploadExam [name="excel_file"]').val()) {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณาเลือกไฟล์</span>', icon: "warning", showConfirmButton: false, timer: 2000 });
            return;
        }
        var fd = new FormData($('#formUploadExam')[0]);
        fd.append('course_id', COURSE_ID);
        fd.append('request_state', 'exam');
        fd.append('request_function', 'upload_exam');
        $.ajax({
            beforeSend: function () { ShowLoadingButton('.BtnUploadExam'); },
            type: "POST", url: "core.php", data: fd, processData: false, contentType: false, dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    bootstrap.Modal.getInstance(document.getElementById('modalUploadExam')).hide();
                    ToastResult(response);
                    LoadExamTab();
                } else { ToastResult(response); }
            },
            complete: function () { HideLoadingButton('.BtnUploadExam'); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
    function ToastResult(response) {
        Swal.fire({
            title: response.result == 1 ? "สำเร็จ" : "แจ้งเตือน",
            html: '<span class="fw-bold ' + (response.result == 1 ? 'text-success' : 'text-danger') + '">' + response.msg + '</span>',
            icon: response.result == 1 ? "success" : "error",
            showConfirmButton: false, timer: 1800, timerProgressBar: true
        });
    }
</script>
