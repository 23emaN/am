<?php $breadcrumbs = [['label' => 'ประวัติการยืนยันตัวตน']]; ?>
<?php include "header.php"; ?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <?php include "navbar.php"; ?>

        <div class="px-2">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center p-4">
                    <h2 class="mb-0">ประวัติการยืนยันตัวตน</h2>
                </div>

                <div class="card-body p-4">
                    <div class="default-table-area">
                        <div class="table-responsive">
                            <table class="table align-middle w-100" id="PageTable">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center" style="width: 60px;">#</th>
                                        <th scope="col">ชื่อ</th>
                                        <th scope="col">เลขบัตรประชาชน</th>
                                        <th scope="col">รายละเอียด</th>
                                        <th scope="col" class="text-center">สถานะ</th>
                                        <th scope="col">ผู้ดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include "footer.php"; ?>

    </div>
</div>

<?php include "script.php"; ?>

</body>

</html>

<script>
    $(document).ready(function () {
        // server-side: ตารางใหญ่ (หลักหมื่นแถว) โหลดทีละหน้าจาก server
        $("#PageTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [[0, 'desc']], // ใหม่สุดก่อน (column 0 = log_id)
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: { url: '../template/assets/js/data-table-th.json' },
            ajax: {
                url: "core.php",
                type: "POST",
                data: function (d) {
                    d.request_state = "verify_history";
                    d.request_function = "get_list_history";
                    return d;
                },
                error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
            },
            columns: [
                { data: "no", className: "text-center", orderable: false },
                { data: "full_name", className: "fw-medium" },
                { data: "citizen_id" },
                { data: "remark", className: "text-secondary" },
                { data: "status", className: "text-center" },
                { data: "admin_name" }
            ]
        });
    });
</script>
