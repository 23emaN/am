<?php $breadcrumbs = [['label' => 'ใบกำกับภาษี (E-Tax)']]; ?>
<?php include "header.php"; ?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <?php include "navbar.php"; ?>

        <div class="px-2">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center p-4">
                    <h4 class="mb-0">ใบกำกับภาษี (E-Tax)</h4>
                </div>

                <div class="card-body p-4">
                    <div class="default-table-area">
                        <div class="table-responsive">
                            <table class="table align-middle w-100" id="PageTable">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center" style="width:60px;">#</th>
                                        <th scope="col">เลขที่เอกสาร</th>
                                        <th scope="col">ชื่อ</th>
                                        <th scope="col">เลขประจำตัวผู้เสียภาษี</th>
                                        <th scope="col">วันที่ในเอกสาร</th>
                                        <th scope="col" class="text-center">สถานะ</th>
                                        <th scope="col" class="text-center" style="width:140px;">ดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody id="EtaxBody"></tbody>
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
        LoadData();
    });

    function LoadData() {
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#PageTable"); },
            type: "POST", url: "core.php",
            data: { request_state: "list_etax", request_function: "get_list_etax" },
            dataType: "json",
            success: function (response) {
                if (response.result == 1) {
                    RenderTable(response.data.list_data);
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + response.msg + '</span>', icon: "error", showConfirmButton: false, timer: 2000 });
                }
            },
            complete: function () { HideLoadingOverlay("#PageTable"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    }

    function statusBadge(s) {
        if (s === "1") { return '<span class="badge bg-success">ออกใบกำกับภาษีแล้ว</span>'; }
        return '<span class="badge bg-danger">ออกใบกำกับไม่สำเร็จ</span>';
    }

    function RenderTable(list) {
        var rows = "";
        (list || []).forEach(function (it, i) {
            // ปุ่มไอคอนสี่เหลี่ยมขนาดเท่ากัน เรียงแถวเดียว — สำเร็จ: ดู+ดาวน์โหลด+ส่งอีเมล, ไม่สำเร็จ: ดูอย่างเดียว
            var sq = 'btn btn-sm d-inline-flex align-items-center justify-content-center p-0';
            var actions = '<div class="d-flex justify-content-center gap-1">' +
                '<a href="etax_view.php?id=' + it.order_id + '" class="' + sq + ' btn-info text-white" style="width:34px;height:34px;" title="ดูข้อมูล">' +
                    '<span class="material-symbols-outlined" style="font-size:18px;">visibility</span></a>';
            if (it.status === "1") {
                actions += '<button type="button" class="' + sq + ' btn-success" style="width:34px;height:34px;" onclick="DownloadEtax(' + it.order_id + ')" title="ดาวน์โหลด PDF">' +
                        '<span class="material-symbols-outlined" style="font-size:18px;">download</span></button>' +
                    '<button type="button" class="' + sq + ' btn-warning" style="width:34px;height:34px;" onclick="SendEmail(' + it.order_id + ')" title="ส่งอีเมล">' +
                        '<span class="material-symbols-outlined" style="font-size:18px;">mail</span></button>';
            }
            actions += '</div>';
            rows +=
                '<tr>' +
                    '<td class="text-center">' + (i + 1) + '</td>' +
                    '<td class="fw-medium">' + EscapeHTML(it.doc_no) + '</td>' +
                    '<td>' + EscapeHTML(it.name) + '</td>' +
                    '<td>' + EscapeHTML(it.tax_id) + '</td>' +
                    '<td>' + EscapeHTML(it.date) + '</td>' +
                    '<td class="text-center">' + statusBadge(it.status) + '</td>' +
                    '<td class="text-center">' + actions + '</td>' +
                '</tr>';
        });
        $("#EtaxBody").html(rows);
        $("#PageTable").DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [[0, 'asc']],
            language: { url: '../template/assets/js/data-table-th.json' },
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "ทั้งหมด"]],
            columnDefs: [{ orderable: false, targets: [6] }]
        });
    }

    // ดูใบกำกับภาษี -> แจ้งรหัสผ่าน (4 ตัวท้ายเลขผู้เสียภาษี) แล้วเปิดหน้าพรีวิว PDF
    function DownloadEtax(order_id) {
        $.ajax({
            type: "POST", url: "core.php",
            data: { request_state: "list_order", request_function: "get_order", order_id: order_id },
            dataType: "json",
            success: function (r) {
                if (r.result != 1) { Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + (r.msg || "ไม่พบข้อมูล") + '</span>', icon: "error" }); return; }
                var taxId = ((r.data.receipt && r.data.receipt.tax_id) || "").replace(/\D/g, "");
                var pass = taxId.length >= 4 ? taxId.slice(-4) : taxId;
                Swal.fire({
                    icon: "success",
                    title: "ดาวน์โหลดใบกำกับภาษี",
                    html: 'รหัสผ่านใบกำกับภาษีของคุณคือ <b style="font-size:1.3em;">' + (pass || "-") + '</b>',
                    confirmButtonText: "ดาวน์โหลด",
                    confirmButtonColor: "#605DFF"
                }).then(function (res) {
                    if (res.isConfirmed) { window.open("pdf_preview.php?type=etax&id=" + order_id, "_blank"); }
                });
            },
            error: function (j, e) { ShowErrorAjax(j, e); }
        });
    }

    // ส่งใบกำกับภาษีทางอีเมลให้ลูกค้า
    function SendEmail(order_id) {
        Swal.fire({
            title: "ส่งใบกำกับภาษีทางอีเมล?",
            html: '<span class="text-secondary">ระบบจะส่งใบกำกับภาษีไปยังอีเมลของลูกค้า</span>',
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "ส่งอีเมล",
            cancelButtonText: "ยกเลิก",
            confirmButtonColor: "#605DFF"
        }).then(function (res) {
            if (!res.isConfirmed) { return; }
            Swal.fire({ title: "กำลังส่งอีเมล...", allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
            $.ajax({
                type: "POST", url: "core.php",
                data: { request_state: "list_etax", request_function: "send_email", order_id: order_id },
                dataType: "json",
                success: function (r) {
                    Swal.close();
                    Swal.fire({
                        title: r.result == 1 ? "สำเร็จ" : "แจ้งเตือน",
                        html: '<span class="fw-bold ' + (r.result == 1 ? 'text-success' : 'text-danger') + '">' + r.msg + '</span>',
                        icon: r.result == 1 ? "success" : "error",
                        showConfirmButton: true
                    });
                },
                error: function (jqXHR, exception) { Swal.close(); ShowErrorAjax(jqXHR, exception); }
            });
        });
    }
</script>
