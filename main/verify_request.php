<?php $breadcrumbs = [['label' => 'คำขอยืนยันตัวตนผู้ใช้งาน']]; ?>
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
                request_state: "verify_request",
                request_function: "get_list_verify",
            },
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    RenderListVerify(response.data);
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: false, allowOutsideClick: false, timer: 2000, timerProgressBar: true });
                }
            },
            complete: function () { HideLoadingOverlay("#GetTable"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    function RenderListVerify(data) {
        const payload = { list_data: data.list_data };

        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#GetTable"); },
            type: "POST",
            url: "view/verifyRequest/GetTable.php",
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
                    columnDefs: [{ orderable: false, targets: [7] }]
                });
            },
            complete: function () { HideLoadingOverlay("#GetTable"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    // ตรวจเอกสาร -> ไปหน้ารายละเอียดผู้ใช้/ลูกค้า แล้วเปิด modal ยืนยันตัวตนอัตโนมัติ
    function OpenVerify(user_id) {
        window.location.href = "user_edit.php?id=" + user_id + "&verify=1";
    }
</script>
