<?php $breadcrumbs = [['label' => 'คอร์สเรียน', 'url' => 'course'], ['label' => 'ประเภทคอร์สเรียน']]; ?>
<?php include "header.php"; ?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <?php include "navbar.php"; ?>

        <div id="GetTable" class="px-2"></div>

        <?php include "footer.php"; ?>
    </div>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content animated fadeIn" id="LoadingMyModal">
            <div id="showModal"></div>
        </div>
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
                request_state: "listCourseType",
                request_function: "get_list_type",
            },
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    RenderList(response.data);
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: false, allowOutsideClick: false, timer: 2000, timerProgressBar: true });
                }
            },
            complete: function () { HideLoadingOverlay("#GetTable"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    function RenderList(data) {
        const payload = { list_data: data.list_data };

        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#GetTable"); },
            type: "POST",
            url: "view/listCourseType/GetTable.php",
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

    function GetModalAdd() {
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#myModal"); },
            type: "POST",
            url: "view/listCourseType/GetModalAdd.php",
            dataType: "html",
            success: function (response) {
                $("#showModal").html(response);
                $("#myModal").modal("show");
            },
            complete: function () { HideLoadingOverlay("#myModal"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    // แก้ไข/ลบ ประเภท — ยังไม่อยู่ในขอบเขตงานนี้ (เหมือนหน้าหมวดหมู่)
    function GetEditType(type_id) {
        Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-secondary">การแก้ไขประเภทอยู่ระหว่างพัฒนา</span>', icon: "info", showConfirmButton: true });
    }
    function GetDeleteType(type_id) {
        Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-secondary">การลบประเภทอยู่ระหว่างพัฒนา</span>', icon: "info", showConfirmButton: true });
    }
</script>
