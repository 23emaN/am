<?php $breadcrumbs = [['label' => 'คำสั่งซื้อทั้งหมด']]; ?>
<?php include "header.php"; ?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <?php include "navbar.php"; ?>

        <div class="px-2">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-3 p-4">
                    <h4 class="mb-0">คำสั่งซื้อทั้งหมด</h4>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#modalDownload">
                        <span class="material-symbols-outlined" style="font-size:18px;">download</span> ดาวน์โหลดรายงานคำสั่งซื้อ
                    </button>
                </div>

                <div class="card-body p-4">
                    <!-- ===== ฟิลเตอร์ค้นหา ===== -->
                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-md-6 col-lg">
                            <label class="form-label fw-medium">หมายเลขคำสั่งซื้อ</label>
                            <input type="text" class="form-control" id="f_order" placeholder="เช่น VAT2606114">
                        </div>
                        <div class="col-md-6 col-lg">
                            <label class="form-label fw-medium">ลูกค้า</label>
                            <input type="text" class="form-control" id="f_customer" placeholder="ชื่อลูกค้า">
                        </div>
                        <div class="col-md-6 col-lg">
                            <label class="form-label fw-medium">สถานะ</label>
                            <select class="form-select" id="f_status">
                                <option value="">ทั้งหมด</option>
                                <option value="1">สำเร็จแล้ว</option>
                                <option value="0">รอชำระเงิน</option>
                                <option value="2">ยกเลิก</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-lg">
                            <label class="form-label fw-medium">สถานะการชำระเงิน</label>
                            <select class="form-select" id="f_payment">
                                <option value="">ทั้งหมด</option>
                                <option value="1">ชำระแล้ว</option>
                                <option value="0">ยังไม่ได้ชำระ</option>
                                <option value="2">ยกเลิก</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-lg">
                            <label class="form-label fw-medium">วันที่สั่งซื้อ</label>
                            <input type="text" class="form-control" id="f_date" placeholder="วัน/เดือน/ปี" autocomplete="off">
                        </div>
                        <div class="col-md-6 col-lg-auto">
                            <button type="button" class="btn btn-primary w-100 px-4" onclick="SearchOrder()">ค้นหา</button>
                        </div>
                    </div>

                    <div class="default-table-area">
                        <div class="table-responsive">
                            <table class="table align-middle w-100" id="PageTable">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center" style="width:60px;">ลำดับ</th>
                                        <th scope="col">หมายเลขคำสั่งซื้อ</th>
                                        <th scope="col">ชื่อลูกค้า</th>
                                        <th scope="col">คอร์สเรียน</th>
                                        <th scope="col" class="text-end">ยอดรวม</th>
                                        <th scope="col" class="text-center">สถานะ</th>
                                        <th scope="col" class="text-center">สถานะการชำระเงิน</th>
                                        <th scope="col">สั่งซื้อเมื่อ</th>
                                        <th scope="col" class="text-center">ดำเนินการ</th>
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

<!-- ===== Modal: ดาวน์โหลดรายงาน ===== -->
<div class="modal fade" id="modalDownload" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ดาวน์โหลดรายงานคำสั่งซื้อ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-medium">เลือกช่วงวันที่</label>
                    <input type="text" class="form-control" id="dl_range" placeholder="เลือกช่วงวันที่ (จาก - ถึง)" autocomplete="off" readonly>
                    <div class="form-text">เลือกได้ทั้งช่วง — คลิกวันเริ่มต้นแล้วคลิกวันสิ้นสุด (เว้นว่าง = ทั้งหมด)</div>
                </div>
                <div class="alert alert-success small mb-0">
                    ระบบจะส่งออกเฉพาะรายการคำสั่งซื้อที่ชำระเงินแล้วและมีสถานะเป็นสำเร็จแล้วเท่านั้น
                </div>
            </div>
            <div class="modal-footer p-3">
                <button type="button" class="btn btn-success w-100" onclick="DownloadReport()">ดาวน์โหลด</button>
            </div>
        </div>
    </div>
</div>

<?php include "script.php"; ?>

</body>

</html>

<script>
    var orderTable = null;
    var dlRangePicker = null;

    $(document).ready(function () {
        // datepicker (รูปแบบ d/m/Y) — ช่องดาวน์โหลดใช้แบบเลือกช่วง (range) เหมือนหน้า home
        if (typeof flatpickr !== "undefined") {
            flatpickr("#f_date", { dateFormat: "d/m/Y", allowInput: true });
            dlRangePicker = flatpickr("#dl_range", { mode: "range", dateFormat: "d/m/Y" });
        }

        orderTable = $("#PageTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [[7, "desc"]], // ใหม่สุดก่อน (สั่งซื้อเมื่อ)
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: { url: '../template/assets/js/data-table-th.json' },
            ajax: {
                url: "core.php",
                type: "POST",
                data: function (d) {
                    d.request_state = "list_order";
                    d.request_function = "get_list_order";
                    d.f_order = $("#f_order").val();
                    d.f_customer = $("#f_customer").val();
                    d.f_status = $("#f_status").val();
                    d.f_payment = $("#f_payment").val();
                    d.f_date = $("#f_date").val();
                    return d;
                },
                error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
            },
            columns: [
                { data: "no", className: "text-center", orderable: false },
                { data: "order_no" },
                { data: "customer", className: "fw-medium" },
                { data: "courses", className: "text-secondary", orderable: false },
                { data: "total", className: "text-end" },
                { data: "status", className: "text-center" },
                { data: "payment", className: "text-center" },
                { data: "created" },
                { data: "action", className: "text-center", orderable: false }
            ]
        });
    });

    function SearchOrder() {
        if (orderTable) { orderTable.ajax.reload(); }
    }

    // ดาวน์โหลดรายงาน Excel (ส่งออกเฉพาะที่ชำระ+สำเร็จ) — ใช้ fetch แล้วบันทึกเป็นไฟล์
    function DownloadReport() {
        // อ่านช่วงวันที่จาก range picker (เหมือน home)
        var from = "", to = "";
        if (dlRangePicker && dlRangePicker.selectedDates.length === 2) {
            from = dlRangePicker.formatDate(dlRangePicker.selectedDates[0], "d/m/Y");
            to = dlRangePicker.formatDate(dlRangePicker.selectedDates[1], "d/m/Y");
        } else if (dlRangePicker && dlRangePicker.selectedDates.length === 1) {
            from = to = dlRangePicker.formatDate(dlRangePicker.selectedDates[0], "d/m/Y");
        }
        var body = new URLSearchParams({
            request_state: "list_order",
            request_function: "export_report",
            from: from, to: to
        });
        Swal.fire({ title: "กำลังสร้างรายงาน...", allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
        fetch("core.php", {
            method: "POST",
            headers: { "Authorization": "Bearer " + (localStorage.getItem("access_token") || "") },
            body: body
        }).then(function (res) {
            var ct = res.headers.get("Content-Type") || "";
            if (ct.indexOf("application/json") !== -1) {
                return res.json().then(function (j) { throw new Error(j.msg || "ดาวน์โหลดไม่สำเร็จ"); });
            }
            return res.blob();
        }).then(function (blob) {
            Swal.close();
            var url = URL.createObjectURL(blob);
            var a = document.createElement("a");
            a.href = url;
            a.download = "order_report.xlsx";
            document.body.appendChild(a); a.click(); a.remove();
            URL.revokeObjectURL(url);
            $("#modalDownload").modal("hide");
        }).catch(function (err) {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + err.message + '</span>', icon: "error", showConfirmButton: true });
        });
    }
</script>
