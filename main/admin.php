<?php $breadcrumbs = [['label' => 'ผู้ดูแลระบบทั้งหมด']]; ?>
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
                request_state: "list_admin",
                request_function: "get_list_admin",
            },
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    RenderListAdmin(response.data);
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: false, allowOutsideClick: false, timer: 2000, timerProgressBar: true });
                }
            },
            complete: function () { HideLoadingOverlay("#GetTable"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    function RenderListAdmin(data) {
        const payload = { list_data: data.list_data, current_admin_id: data.current_admin_id };

        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#GetTable"); },
            type: "POST",
            url: "view/listAdmin/GetTable.php",
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
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "ทั้งหมด"]],
                    columnDefs: [{ orderable: false, targets: [3] }]
                });
            },
            complete: function () { HideLoadingOverlay("#GetTable"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    // แก้ไข -> หน้ารายละเอียดผู้ดูแลระบบ
    function GetEditAdmin(user_id) {
        window.location.href = "admin_edit.php?id=" + user_id;
    }

    function DeleteAdmin(user_id) {
        Swal.fire({
            title: "ลบผู้ดูแลระบบ?",
            html: '<span class="text-secondary">ระบบจะลบบัญชีผู้ดูแลระบบนี้ (soft delete)</span>',
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "ลบ",
            cancelButtonText: "ยกเลิก",
            confirmButtonColor: "#dc3545"
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                type: "POST",
                url: "core.php",
                data: { request_state: "list_admin", request_function: "delete_admin", user_id: user_id },
                dataType: "json",
                success: function (response) {
                    if (response.result == 1) {
                        Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + response.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1500, timerProgressBar: true, didClose: function () { LoadData(); } });
                    } else {
                        Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", confirmButtonText: "ตกลง" });
                    }
                },
                error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
            });
        });
    }
</script>
