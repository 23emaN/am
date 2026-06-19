<?php $breadcrumbs = [['label' => 'คอร์สเรียน']]; ?>
<?php include "header.php"; ?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <?php include "navbar.php"; ?>

        <div id="GetTable" class="px-2"></div>

        <?php include "footer.php"; ?>

    </div>
</div>

<?php include "script.php"; ?>

</body>

</html>

<script>
    $(document).ready(function () {
        LoadData();
    });

    function LoadData() {
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#GetTable"); },
            type: "POST",
            url: "core.php",
            data: {
                request_state: "list_course",
                request_function: "get_list_course",
            },
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    RenderListCourse(response.data);
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: false, allowOutsideClick: false, timer: 2000, timerProgressBar: true });
                }
            },
            complete: function () { HideLoadingOverlay("#GetTable"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    function RenderListCourse(data) {
        const payload = { list_data: data.list_data };

        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#GetTable"); },
            type: "POST",
            url: "view/listCourse/GetTable.php",
            data: JSON.stringify(payload),
            contentType: "application/json; charset=utf-8",
            processData: false,
            dataType: "html",
            success: function (response) {
                $("#GetTable").html(response);
                $("#PageTable").DataTable({
                    responsive: true,
                    autoWidth: false,
                    pageLength: 10,
                    language: { url: '../template/assets/js/data-table-th.json' },
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "ทั้งหมด"]]
                });
            },
            complete: function () { HideLoadingOverlay("#GetTable"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    // ปุ่มแก้ไข — ยังไม่อยู่ในขอบเขตงานนี้ (list + เพิ่มคอร์ส)
    function GetEditCourse(course_id) {
        Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-secondary">หน้าแก้ไขคอร์สเรียนอยู่ระหว่างพัฒนา</span>', icon: "info", showConfirmButton: true });
    }
</script>
