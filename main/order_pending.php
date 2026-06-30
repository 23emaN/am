<?php $breadcrumbs = [['label' => 'คำสั่งซื้อรอยืนยัน']]; ?>
<?php include "header.php"; ?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <?php include "navbar.php"; ?>
        <div class="px-2">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-3 p-4">
                    <h4 class="mb-0">คำสั่งซื้อรอยืนยัน</h4>
                </div>

                <div class="card-body p-4">
                    <!-- ===== FILTER ROW ===== -->
                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-md-6 col-lg">
                            <label class="form-label fw-medium">หมายเลขคำสั่งซื้อ</label>
                            <input type="text" class="form-control" id="f_order" placeholder="เช่น ORD-..." autocomplete="off">
                        </div>
                        <div class="col-md-6 col-lg">
                            <label class="form-label fw-medium">ลูกค้า</label>
                            <input type="text" class="form-control" id="f_customer" placeholder="ชื่อลูกค้า" autocomplete="off">
                        </div>
                        <div class="col-md-6 col-lg">
                            <label class="form-label fw-medium">วันที่สั่งซื้อ</label>
                            <input type="text" class="form-control" id="f_date" placeholder="วัน/เดือน/ปี" autocomplete="off">
                        </div>
                        <div class="col-md-6 col-lg-auto">
                            <button type="button" class="btn btn-primary w-100 px-4" onclick="SearchOrder()">ค้นหา</button>
                        </div>
                    </div>

                    <!-- TABLE -->
                    <div class="default-table-area">
                        <div class="table-responsive">
                            <table class="table align-middle w-100" id="PageTable">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center" style="width:60px;">ลำดับ</th>
                                        <th scope="col" style="min-width:160px;">ชื่อลูกค้า</th>
                                        <th scope="col" style="min-width:280px;">คอร์สเรียน</th>
                                        <th scope="col" class="text-end text-nowrap" style="width:1%;">ยอดรวม</th>
                                        <th scope="col" class="text-nowrap" style="width:1%;">สั่งซื้อเมื่อ</th>
                                        <th scope="col" class="text-center text-nowrap" style="width:1%;">ดำเนินการ</th>
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
    var orderTable = null;

    $(document).ready(function () {
        if (typeof flatpickr !== "undefined") {
            flatpickr("#f_date", { dateFormat: "d/m/Y", allowInput: true });
        }

        orderTable = $("#PageTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [[4, "desc"]], // ใหม่สุดก่อน (สั่งซื้อเมื่อ)
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: { url: '../template/assets/js/data-table-th.json' },
            ajax: {
                url: "core.php",
                type: "POST",
                data: function (d) {
                    d.request_state = "list_order";
                    d.request_function = "get_list_pending";
                    d.f_order = $("#f_order").val();
                    d.f_customer = $("#f_customer").val();
                    d.f_date = $("#f_date").val();
                    return d;
                },
                error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
            },
            columns: [
                { data: "no", className: "text-center", orderable: false },
                { data: "customer", className: "fw-medium" },
                { data: "courses", className: "text-secondary", orderable: false },
                { data: "total", className: "text-end text-nowrap" },
                { data: "created", className: "text-nowrap" },
                { data: "action", className: "text-center text-nowrap", orderable: false }
            ]
        });
    });

    function SearchOrder() {
        if (orderTable) { orderTable.ajax.reload(); }
    }

    // ยกเลิกคำสั่งซื้อจากตาราง
    function CancelOrderRow(orderId) {
        Swal.fire({
            title: "ยืนยันการยกเลิก",
            html: '<span class="text-secondary">ต้องการยกเลิกคำสั่งซื้อนี้ใช่หรือไม่?</span>',
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "ยกเลิกคำสั่งซื้อ",
            cancelButtonText: "ปิด",
            confirmButtonColor: "#dc3545"
        }).then(function (result) {
            if (!result.isConfirmed) { return; }
            $.ajax({
                type: "POST", url: "core.php",
                data: { request_state: "list_order", request_function: "cancel_order", order_id: orderId },
                dataType: "json",
                success: function (res) {
                    if (res.result == 1) {
                        Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + res.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1500 })
                            .then(function () { if (orderTable) { orderTable.ajax.reload(null, false); } });
                    } else {
                        Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + res.msg + '</span>', icon: "error", showConfirmButton: true });
                    }
                },
                error: function (j, e) { ShowErrorAjax(j, e); }
            });
        });
    }
</script>
