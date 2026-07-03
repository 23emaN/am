<?php
    $course_id = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;
    $lesson_id = isset($_GET['lesson_id']) ? (int) $_GET['lesson_id'] : 0;
    $breadcrumbs = [
        ['label' => 'คอร์สเรียน', 'url' => 'course'],
        ['label' => 'แก้ไขคอร์สเรียน #' . $course_id, 'url' => 'course_edit.php?id=' . $course_id],
        ['label' => 'จัดการบทเรียน'],
    ];
?>
<?php include "header.php"; ?>

<style>
    #editor_question_text { height: 200px; background:#fff; }
    .ql-toolbar.ql-snow, .ql-container.ql-snow { border-color: #ced4da; }
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <?php include "navbar.php"; ?>

        <div class="px-2">
            <div class="card app-card bg-white border-0 rounded-3 mb-3">
                <div class="card-body p-4">
                    <h2 class="mb-3">จัดการบทเรียน</h2>

                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-l-general" type="button">ทั่วไป</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-l-video" type="button">วีดีโอ</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-l-question" type="button">คำถามระหว่างรับชม</button></li>
                    </ul>

                    <div class="tab-content">
                        <!-- ===== ทั่วไป ===== -->
                        <div class="tab-pane fade show active" id="tab-l-general" role="tabpanel">
                            <form id="formLesson">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">ลำดับ/บทเรียนที่ <span class="text-danger">*</span></label>
                                        <input type="number" min="0" class="form-control" name="lesson_order">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">ชื่อบทเรียน <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="lesson_name">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-medium">คำถามระหว่างรับชม <span class="text-danger">*</span></label>
                                        <select class="form-select" name="lesson_question">
                                            <option value="0">ปิดใช้งานคำถาม</option>
                                            <option value="1">เปิดใช้งานคำถาม</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">จำนวนคำถามระหว่างรับชม</label>
                                        <input type="number" min="0" class="form-control" name="lesson_question_limit">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">ระยะเวลาทำคำถามระหว่างรับชม (วินาที)</label>
                                        <input type="number" min="0" class="form-control" name="lesson_question_time">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-medium">รายละเอียดโดยย่อ</label>
                                        <textarea class="form-control" name="lesson_overview" rows="3"></textarea>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary w-100 mt-3 BtnUpdateLesson" onclick="SubmitUpdateLesson()">แก้ไขข้อมูล</button>
                            </form>
                        </div>

                        <!-- ===== วีดีโอ ===== -->
                        <div class="tab-pane fade" id="tab-l-video" role="tabpanel">
                            <form id="formVideo" enctype="multipart/form-data">
                                <label class="form-label fw-medium">อัพโหลดวีดีโอ</label>
                                <input type="file" class="form-control" name="video_file" accept="video/*">
                                <div class="form-text">อัปโหลดไฟล์วิดีโอ → ระบบจะอัปขึ้น Vimeo ให้อัตโนมัติแล้วเก็บลิงก์ (รองรับ mp4, mov, avi, wmv, mkv, webm)</div>
                                <button type="button" class="btn btn-primary w-100 mt-3 BtnUploadVideo" onclick="SubmitUploadVideo()">อัพโหลดวีดีโอขึ้น Vimeo</button>
                            </form>

                            <!-- ปุ่มไปหน้าทดสอบบทเรียน (เครื่องเล่นจริง + คำถามคั่น) -->
                            <a href="lesson_preview.php?course_id=<?php echo $course_id; ?>&lesson_id=<?php echo $lesson_id; ?>"
                               class="btn btn-outline-success w-100 mt-2 d-inline-flex align-items-center justify-content-center gap-1">
                                <span class="material-symbols-outlined" style="font-size:18px;" aria-hidden="true">play_circle</span>
                                ทดสอบเล่นบทเรียน (Preview)
                            </a>

                            <div id="videoPreview" class="mt-4"></div>
                        </div>

                        <!-- ===== คำถามระหว่างรับชม ===== -->
                        <div class="tab-pane fade" id="tab-l-question" role="tabpanel">
                            <div id="GetQuestionTab"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include "footer.php"; ?>

    </div>
</div>

<!-- ===== Modal: เพิ่ม/แก้ไขคำถาม ===== -->
<div class="modal fade" id="modalQuestion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalQuestionTitle">สร้างคำถามใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formQuestion" enctype="multipart/form-data">
                    <input type="hidden" name="question_id" id="q_id">
                    <input type="hidden" name="question_text" id="q_text">
                    <div class="mb-3">
                        <label class="form-label fw-medium">คำถาม <span class="text-danger">*</span></label>
                        <div id="editor_question_text"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">ภาพ</label>
                        <input type="file" class="form-control" name="question_image" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">ไฟล์</label>
                        <input type="file" class="form-control" name="question_file">
                    </div>
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <label class="form-label fw-medium mb-0">ตัวเลือก <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-sm btn-success" onclick="AddChoiceRow('')">เพิ่มตัวเลือก</button>
                    </div>
                    <div id="choiceList"></div>
                    <div class="mb-2">
                        <label for="correctSelect" class="form-label fw-medium">คำตอบที่ถูกต้อง <span class="text-danger">*</span></label>
                        <select class="form-select" id="correctSelect"></select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success w-100 BtnSaveQuestion" onclick="SubmitQuestion()">บันทึกคำถาม</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== Modal: อัพโหลด Excel ===== -->
<div class="modal fade" id="modalUploadQuestion" tabindex="-1" aria-hidden="true">
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
                <form id="formUploadQuestion" enctype="multipart/form-data">
                    <label class="form-label fw-medium">ไฟล์ข้อสอบ</label>
                    <input type="file" class="form-control" name="excel_file" accept=".xlsx,.xls">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100 BtnUploadQuestion" onclick="SubmitUploadQuestion()">อัพโหลด</button>
            </div>
        </div>
    </div>
</div>

<?php include "script.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/tus-js-client@4.1.0/dist/tus.min.js"></script>

</body>

</html>

<script>
    var COURSE_ID = <?php echo $course_id; ?>;
    var LESSON_ID = <?php echo $lesson_id; ?>;
    var quillQuestion = null;
    var questionTabLoaded = false;

    $(document).ready(function () {
        if (!COURSE_ID || !LESSON_ID) {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">ข้อมูลไม่ครบ</span>', icon: "error", showConfirmButton: true })
                .then(() => { window.location.href = "course.php"; });
            return;
        }
        LoadLesson();
        $('button[data-bs-target="#tab-l-question"]').on('shown.bs.tab', function () {
            if (!questionTabLoaded) { questionTabLoaded = true; LoadQuestionTab(); }
        });
    });

    // ===== โหลดข้อมูลบทเรียน (เติมแท็บทั่วไป + วีดีโอ) =====
    function LoadLesson() {
        $.ajax({
            type: "POST", url: "core.php",
            data: { request_state: "lesson", request_function: "get_lesson", lesson_id: LESSON_ID },
            dataType: "json",
            success: function (response) {
                if (response.result != 1) {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: true })
                        .then(() => { window.location.href = "course_edit.php?id=" + COURSE_ID; });
                    return;
                }
                var L = response.data.lesson;
                $('#formLesson [name="lesson_order"]').val(L.lesson_order);
                $('#formLesson [name="lesson_name"]').val(L.lesson_name);
                $('#formLesson [name="lesson_question"]').val(L.lesson_question || '0');
                $('#formLesson [name="lesson_question_limit"]').val(L.lesson_question_limit);
                $('#formLesson [name="lesson_question_time"]').val(L.lesson_question_time);
                $('#formLesson [name="lesson_overview"]').val(L.lesson_overview || '');
                $('#formVideo [name="lesson_video"]').val(L.lesson_video || '');
                if (L.lesson_video) {
                    $('#videoPreview').html('<label class="form-label fw-medium">วิดีโอปัจจุบัน</label><div class="ratio ratio-16x9"><iframe src="' + EscapeHTML(L.lesson_video) + '" allowfullscreen></iframe></div>');
                }
            },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    function SubmitUpdateLesson() {
        var name = $('#formLesson [name="lesson_name"]').val().trim();
        if (name === "") {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณากรอกชื่อบทเรียน</span>', icon: "warning", showConfirmButton: false, timer: 2000 });
            return;
        }
        var data = $('#formLesson').serializeArray();
        data.push({ name: "request_state", value: "lesson" });
        data.push({ name: "request_function", value: "update_lesson" });
        data.push({ name: "lesson_id", value: LESSON_ID });
        $.ajax({
            beforeSend: function () { ShowLoadingButton('.BtnUpdateLesson'); },
            type: "POST", url: "core.php", data: $.param(data), dataType: "json",
            success: function (response) { ToastResult(response); },
            complete: function () { HideLoadingButton('.BtnUpdateLesson'); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    function SubmitUploadVideo() {
        var fileInput = $('#formVideo [name="video_file"]')[0];
        if (!fileInput || !fileInput.files || !fileInput.files.length) {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณาเลือกไฟล์วิดีโอ</span>', icon: "warning", showConfirmButton: false, timer: 2000 });
            return;
        }
        if (typeof tus === "undefined") {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">ไลบรารีอัปโหลดยังไม่พร้อม (ตรวจสอบอินเทอร์เน็ต)</span>', icon: "error", showConfirmButton: true });
            return;
        }
        var file = fileInput.files[0];

        Swal.fire({
            title: "กำลังอัปโหลดวีดีโอขึ้น Vimeo",
            html:
                '<div class="text-secondary mb-2" id="upPhase">กำลังเตรียมการอัปโหลด...</div>' +
                '<div class="progress" style="height:20px;border-radius:10px;">' +
                '<div id="upBar" class="progress-bar progress-bar-striped progress-bar-animated" ' +
                'role="progressbar" style="width:0%;background:#605DFF;">0%</div></div>',
            allowOutsideClick: false,
            showConfirmButton: false
        });

        // 1) สร้างงานอัปโหลดบน Vimeo (ขอ upload_link)
        $.ajax({
            type: "POST", url: "core.php",
            data: { request_state: "lesson", request_function: "create_upload", lesson_id: LESSON_ID, size: file.size },
            dataType: "json"
        }).done(function (res) {
            if (res.result != 1) { Swal.close(); ToastResult(res); return; }

            var uploadLink = res.data.upload_link;
            var videoUri = res.data.video_uri;
            $("#upPhase").text("กำลังอัปโหลดไฟล์ขึ้น Vimeo...");

            // 2) อัปไฟล์ตรงไป Vimeo ผ่าน tus -> progress จริง
            var upload = new tus.Upload(file, {
                uploadUrl: uploadLink,
                retryDelays: [0, 1000, 3000, 5000],
                onProgress: function (uploaded, total) {
                    var pct = Math.round((uploaded / total) * 100);
                    $("#upBar").css("width", pct + "%").text(pct + "%");
                },
                onError: function (error) {
                    Swal.close();
                    Swal.fire({ title: "อัปโหลดล้มเหลว", html: '<span class="fw-bold text-danger">' + error + '</span>', icon: "error", showConfirmButton: true });
                },
                onSuccess: function () {
                    $("#upBar").css("width", "100%").text("100%");
                    $("#upPhase").text("อัปโหลดเสร็จ — กำลังบันทึกลิงก์...");
                    // 3) ให้ server ดึง embed URL (มี hash) มาเก็บใน DB
                    $.ajax({
                        type: "POST", url: "core.php",
                        data: { request_state: "lesson", request_function: "finish_upload", lesson_id: LESSON_ID, video_uri: videoUri },
                        dataType: "json"
                    }).done(function (fin) {
                        Swal.close(); ToastResult(fin);
                        if (fin.result == 1) { LoadLesson(); } // อัปเสร็จ -> แสดงวิดีโอที่อัปทันที
                    }).fail(function (j, e) { Swal.close(); ShowErrorAjax(j, e); });
                }
            });
            upload.start();
        }).fail(function (j, e) { Swal.close(); ShowErrorAjax(j, e); });
    }

    // ===== แท็บคำถาม =====
    function LoadQuestionTab() {
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#GetQuestionTab"); },
            type: "POST", url: "core.php",
            data: { request_state: "question", request_function: "get_list_question", lesson_id: LESSON_ID },
            dataType: "json",
            success: function (response) {
                if (response.result == 1) { RenderQuestionTable(response.data.list_data); }
                else { ToastResult(response); }
            },
            complete: function () { HideLoadingOverlay("#GetQuestionTab"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
    function RenderQuestionTable(list_data) {
        $.ajax({
            type: "POST", url: "view/question/GetTable.php",
            data: JSON.stringify({ list_data: list_data }),
            contentType: "application/json; charset=utf-8", processData: false, dataType: "html",
            success: function (html) { $("#GetQuestionTab").html(html); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    function EnsureQuill() {
        if (!quillQuestion && typeof Quill !== 'undefined') {
            quillQuestion = new Quill('#editor_question_text', {
                theme: 'snow',
                modules: { toolbar: [['bold', 'italic', 'underline'], [{ 'list': 'ordered' }, { 'list': 'bullet' }], ['link'], ['clean']] }
            });
        }
    }
    function AddChoiceRow(text) {
        var idx = $('#choiceList .choice-row').length + 1;
        var row = $('<div class="input-group mb-2 choice-row">' +
            '<span class="input-group-text">ข้อ ' + idx + '</span>' +
            '<textarea class="form-control" name="choice_text[]" rows="1"></textarea>' +
            '<button type="button" class="btn btn-outline-danger" onclick="RemoveChoiceRow(this)">ลบ</button>' +
            '</div>');
        row.find('textarea').val(text || '');
        $('#choiceList').append(row);
        RefreshCorrectOptions();
    }
    function RemoveChoiceRow(btn) {
        $(btn).closest('.choice-row').remove();
        ReindexChoices();
        RefreshCorrectOptions();
    }
    function ReindexChoices() {
        $('#choiceList .choice-row').each(function (i) {
            $(this).find('.input-group-text').text('ข้อ ' + (i + 1));
        });
    }
    function RefreshCorrectOptions() {
        var prev = $('#correctSelect').val();
        var n = $('#choiceList .choice-row').length;
        var html = '<option value="">---กรุณาเลือกคำตอบที่ถูก---</option>';
        for (var i = 1; i <= n; i++) { html += '<option value="' + i + '">ตัวเลือกที่ ' + i + '</option>'; }
        $('#correctSelect').html(html);
        if (prev && prev <= n) { $('#correctSelect').val(prev); }
    }

    function OpenAddQuestion() {
        $('#modalQuestionTitle').text('สร้างคำถามใหม่');
        $('#formQuestion')[0].reset();
        $('#q_id').val('');
        EnsureQuill();
        if (quillQuestion) { quillQuestion.setText(''); }
        $('#choiceList').empty();
        AddChoiceRow(''); AddChoiceRow('');
        RefreshCorrectOptions();
        new bootstrap.Modal(document.getElementById('modalQuestion')).show();
    }
    function OpenEditQuestion(qid) {
        $.ajax({
            type: "POST", url: "core.php",
            data: { request_state: "question", request_function: "get_question", question_id: qid },
            dataType: "json",
            success: function (response) {
                if (response.result != 1) { ToastResult(response); return; }
                $('#modalQuestionTitle').text('แก้ไขคำถาม');
                $('#formQuestion')[0].reset();
                $('#q_id').val(qid);
                EnsureQuill();
                if (quillQuestion) { quillQuestion.root.innerHTML = response.data.question.question_text || ''; }
                $('#choiceList').empty();
                var correctIdx = 0;
                response.data.choices.forEach(function (c, i) {
                    AddChoiceRow(c.question_choice_text || '');
                    if (String(c.question_choice_correct) === '1') { correctIdx = i + 1; }
                });
                RefreshCorrectOptions();
                if (correctIdx > 0) { $('#correctSelect').val(correctIdx); }
                new bootstrap.Modal(document.getElementById('modalQuestion')).show();
            },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
    function SubmitQuestion() {
        if (quillQuestion) {
            var html = quillQuestion.root.innerHTML;
            if (quillQuestion.getText().trim() === '') { html = ''; }
            $('#q_text').val(html);
        }
        if ($('#q_text').val().trim() === '') {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณากรอกคำถาม</span>', icon: "warning", showConfirmButton: false, timer: 2000 });
            return;
        }
        if (!$('#correctSelect').val()) {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณาเลือกคำตอบที่ถูกต้อง</span>', icon: "warning", showConfirmButton: false, timer: 2000 });
            return;
        }
        var isEdit = $('#q_id').val() !== '';
        var fd = new FormData($('#formQuestion')[0]);
        fd.append('correct', $('#correctSelect').val());
        fd.append('lesson_id', LESSON_ID);
        fd.append('request_state', 'question');
        fd.append('request_function', isEdit ? 'update_question' : 'add_question');
        $.ajax({
            beforeSend: function () { ShowLoadingButton('.BtnSaveQuestion'); },
            type: "POST", url: "core.php", data: fd, processData: false, contentType: false, dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    bootstrap.Modal.getInstance(document.getElementById('modalQuestion')).hide();
                    ToastResult(response);
                    LoadQuestionTab();
                } else { ToastResult(response); }
            },
            complete: function () { HideLoadingButton('.BtnSaveQuestion'); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }
    function DeleteQuestion(qid) {
        Swal.fire({ title: "ยืนยันการลบ", html: '<span class="fw-bold text-danger">ต้องการลบคำถามนี้ใช่หรือไม่?</span>', icon: "warning", showCancelButton: true, confirmButtonText: "ลบ", cancelButtonText: "ยกเลิก", confirmButtonColor: "#dc3545" })
        .then((res) => {
            if (!res.isConfirmed) { return; }
            $.ajax({
                type: "POST", url: "core.php",
                data: { request_state: "question", request_function: "delete_question", question_id: qid },
                dataType: "json",
                success: function (response) { ToastResult(response); if (response.result == 1) { LoadQuestionTab(); } },
                error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
            });
        });
    }

    // ===== อัพโหลด Excel =====
    function OpenUploadQuestion() {
        $('#formUploadQuestion')[0].reset();
        new bootstrap.Modal(document.getElementById('modalUploadQuestion')).show();
    }
    function SubmitUploadQuestion() {
        if (!$('#formUploadQuestion [name="excel_file"]').val()) {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">กรุณาเลือกไฟล์</span>', icon: "warning", showConfirmButton: false, timer: 2000 });
            return;
        }
        var fd = new FormData($('#formUploadQuestion')[0]);
        fd.append('lesson_id', LESSON_ID);
        fd.append('request_state', 'question');
        fd.append('request_function', 'upload_question');
        $.ajax({
            beforeSend: function () { ShowLoadingButton('.BtnUploadQuestion'); },
            type: "POST", url: "core.php", data: fd, processData: false, contentType: false, dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    bootstrap.Modal.getInstance(document.getElementById('modalUploadQuestion')).hide();
                    ToastResult(response);
                    LoadQuestionTab();
                } else { ToastResult(response); }
            },
            complete: function () { HideLoadingButton('.BtnUploadQuestion'); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    // helper แจ้งผล
    function ToastResult(response) {
        Swal.fire({
            title: response.result == 1 ? "สำเร็จ" : "แจ้งเตือน",
            html: '<span class="fw-bold ' + (response.result == 1 ? 'text-success' : 'text-danger') + '">' + response.msg + '</span>',
            icon: response.result == 1 ? "success" : "error",
            showConfirmButton: false, timer: 1800, timerProgressBar: true
        });
    }
</script>
