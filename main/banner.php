<?php $breadcrumbs = [['label' => 'แบนเนอร์ทั้งหมด']]; ?>
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
                request_state: "list_banner",
                request_function: "get_list_banner",
            },
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    RenderListBanner(response.data);
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: false, allowOutsideClick: false, timer: 2000, timerProgressBar: true });
                }
            },
            complete: function () { HideLoadingOverlay("#GetTable"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    function RenderListBanner(data) {
        const payload = { list_data: data.list_data };

        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#GetTable"); },
            type: "POST",
            url: "view/listBanner/GetTable.php",
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
                    columnDefs: [{ orderable: false, targets: [1, 3, 4] }]
                });
            },
            complete: function () { HideLoadingOverlay("#GetTable"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    // ไปหน้าแก้ไขแบนเนอร์
    function GetEditBanner(banner_id) {
        window.location.href = "banner_edit.php?id=" + banner_id;
    }
</script>
