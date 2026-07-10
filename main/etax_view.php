<?php
    // ดูข้อมูลใบกำกับภาษี (E-TAX) — read-only
    // ข้อมูลลูกค้า/ที่อยู่จาก tbl_user_address, รายการจากออเดอร์ (ผ่าน get_order)
    // เลขเอกสาร/สถานะ/ส่งข้อมูล = mock (ยังไม่มีระบบ e-Tax จริง)
    $order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $doc_no   = 'ET' . date('ym') . str_pad((string) $order_id, 7, '0', STR_PAD_LEFT);
    $breadcrumbs = [['label' => 'ดูข้อมูลใบกำกับภาษี (E-TAX)']];
?>
<?php include "header.php"; ?>

<style>
    .etx-row { display: flex; gap: 10px; padding: 7px 0; }
    .etx-row .etx-label { color: #44516d; font-weight: 600; min-width: 150px; flex-shrink: 0; }
    .etx-row .etx-value { flex: 1; word-break: break-word; }
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <?php include "navbar.php"; ?>

        <div class="px-2">
            <div class="card app-card bg-white border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <a href="etax" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1"><span class="material-symbols-outlined" style="font-size:18px;" aria-hidden="true">arrow_back</span> กลับ</a>
                        <h4 class="mb-0">ดูข้อมูลใบกำกับภาษี (E-TAX)</h4>
                    </div>

                    <!-- ปุ่มจัดการ (เปลี่ยนตามสถานะ; ไม่มี "ดูข้อผิดพลาด" ตามที่กำหนด) -->
                    <div class="d-flex flex-wrap justify-content-end gap-2 mb-4" id="EtxButtons"></div>

                    <!-- ข้อมูลหัวเอกสาร -->
                    <div class="row g-2 mb-4">
                        <div class="col-lg-6">
                            <div class="etx-row"><div class="etx-label">เลขที่เอกสาร:</div><div class="etx-value"><?php echo htmlspecialchars($doc_no); ?></div></div>
                            <div class="etx-row"><div class="etx-label">ประเภทลูกค้า:</div><div class="etx-value" id="EtxType">-</div></div>
                            <div class="etx-row"><div class="etx-label">หมายเลขผู้เสียภาษี:</div><div class="etx-value" id="EtxTaxId">-</div></div>
                            <div class="etx-row"><div class="etx-label">สาขา:</div><div class="etx-value" id="EtxBranch">-</div></div>
                            <div class="etx-row"><div class="etx-label">สถานะ:</div><div class="etx-value" id="EtxStatus">-</div></div>
                        </div>
                        <div class="col-lg-6">
                            <div class="etx-row"><div class="etx-label">วันที่ในเอกสาร:</div><div class="etx-value"><?php echo date('d/m/Y'); ?></div></div>
                            <div class="etx-row"><div class="etx-label">ชื่อลูกค้า:</div><div class="etx-value" id="EtxName">-</div></div>
                            <div class="etx-row"><div class="etx-label">ที่อยู่:</div><div class="etx-value" id="EtxAddress">-</div></div>
                            <div class="etx-row"><div class="etx-label">เบอร์โทร:</div><div class="etx-value" id="EtxPhone">-</div></div>
                            <div class="etx-row"><div class="etx-label">อีเมล:</div><div class="etx-value" id="EtxEmail">-</div></div>
                        </div>
                    </div>

                    <!-- รายการสินค้า -->
                    <h6 class="mb-3">รายการสินค้า:</h6>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="text-secondary">
                                    <th class="text-center" style="width:60px;">ลำดับ</th>
                                    <th>ชื่อสินค้า/บริการ</th>
                                    <th class="text-center">จำนวน</th>
                                    <th class="text-end">ราคาสินค้า</th>
                                    <th class="text-end">ส่วนลด</th>
                                    <th class="text-end">VAT</th>
                                    <th class="text-end">รวม</th>
                                    <th>ประเภทภาษี</th>
                                </tr>
                            </thead>
                            <tbody id="EtxItems"></tbody>
                            <tfoot id="EtxFoot"></tfoot>
                        </table>
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
    var ORDER_ID = <?php echo $order_id; ?>;

    function money(n) { return (typeof NumberFormat === "function" ? NumberFormat(n, 2) : Number(n).toFixed(2)) + " ฿"; }

    $(document).ready(function () {
        if (!ORDER_ID) {
            Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">ไม่พบรหัสคำสั่งซื้อ</span>', icon: "error", showConfirmButton: true })
                .then(function () { window.location.href = "order"; });
            return;
        }
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("body"); },
            type: "POST", url: "core.php",
            data: { request_state: "list_order", request_function: "get_order", order_id: ORDER_ID },
            dataType: "json",
            success: function (res) {
                if (res.result != 1) {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + res.msg + '</span>', icon: "error", showConfirmButton: true })
                        .then(function () { window.location.href = "order"; });
                    return;
                }
                Render(res.data);
            },
            complete: function () { HideLoadingOverlay("body"); },
            error: function (jqXHR, exception) { ShowErrorAjax(jqXHR, exception); }
        });
    });

    function Render(d) {
        var o = d.order, rc = d.receipt, sm = d.summary;

        $("#EtxType").text(rc.type);
        $("#EtxTaxId").text(rc.tax_id);
        $("#EtxBranch").text(rc.branch || "-");
        $("#EtxName").text(rc.name);
        $("#EtxAddress").text(rc.address);
        $("#EtxPhone").text(rc.phone || "-");
        $("#EtxEmail").text((d.receipt_raw && d.receipt_raw.email) ? d.receipt_raw.email : "-");

        // ออกสำเร็จ = ลูกค้ามีเลขผู้เสียภาษี (ข้อมูลใบกำกับครบ)
        var success = rc.tax_id && rc.tax_id !== "-";
        if (success) {
            $("#EtxStatus").html('<span class="badge bg-success">ออกใบกำกับภาษีแล้ว</span>');
            $("#EtxButtons").html(
                '<button type="button" class="btn btn-warning" onclick="SendEmail()">ส่งอีเมลอีกครั้ง</button>' +
                '<button type="button" class="btn btn-success" onclick="DownloadEtax(' + o.order_id + ')">ดาวน์โหลดใบกำกับภาษี</button>'
            );
        } else {
            $("#EtxStatus").html('<span class="badge bg-danger">ล้มเหลว (สามารถออกใหม่ได้อีกครั้ง)</span>');
            $("#EtxButtons").html(
                '<button type="button" class="btn btn-warning" onclick="ComingSoon()">ส่งข้อมูลใบกำกับภาษีใหม่อีกครั้ง</button>' +
                '<a href="etax_edit.php?id=' + o.order_id + '" class="btn btn-secondary">แก้ไขใบกำกับภาษี</a>'
            );
        }

        // รายการ (ราคาสินค้า = ก่อน VAT, VAT 7% ต่อรายการ)
        var rows = "";
        d.items.forEach(function (it, i) {
            var before = it.price / 1.07, vat = it.price - before;
            rows +=
                '<tr>' +
                    '<td class="text-center">' + (i + 1) + '</td>' +
                    '<td>' + EscapeHTML(it.course_name) + '</td>' +
                    '<td class="text-center">1</td>' +
                    '<td class="text-end">' + money(before) + '</td>' +
                    '<td class="text-end">' + money(0) + '</td>' +
                    '<td class="text-end">' + money(vat) + '</td>' +
                    '<td class="text-end">' + money(it.price) + '</td>' +
                    '<td>ภาษีมูลค่าเพิ่ม 7%</td>' +
                '</tr>';
        });
        if (!d.items.length) { rows = '<tr><td colspan="8" class="text-center text-muted">ไม่มีรายการ</td></tr>'; }
        $("#EtxItems").html(rows);

        $("#EtxFoot").html(
            '<tr><td colspan="6"></td><td class="text-secondary">รวม</td><td class="fw-bold text-end">' + money(sm.before) + '</td></tr>' +
            '<tr><td colspan="6"></td><td class="text-secondary">VAT</td><td class="fw-bold text-end">' + money(sm.vat) + '</td></tr>' +
            '<tr><td colspan="6"></td><td class="fw-bold">รวมทั้งสิ้น</td><td class="fw-bold text-end text-primary">' + money(sm.total) + '</td></tr>'
        );
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
    function SendEmail() {
        Swal.fire({
            title: "ส่งใบกำกับภาษีทางอีเมล?",
            html: '<span class="text-secondary">ระบบจะส่งใบกำกับภาษีไปยังอีเมลของลูกค้า</span>',
            icon: "question", showCancelButton: true, confirmButtonText: "ส่งอีเมล", cancelButtonText: "ยกเลิก", confirmButtonColor: "#605DFF"
        }).then(function (res) {
            if (!res.isConfirmed) { return; }
            Swal.fire({ title: "กำลังส่งอีเมล...", allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
            $.ajax({
                type: "POST", url: "core.php",
                data: { request_state: "list_etax", request_function: "send_email", order_id: ORDER_ID },
                dataType: "json",
                success: function (r) {
                    Swal.close();
                    Swal.fire({ title: r.result == 1 ? "สำเร็จ" : "แจ้งเตือน", html: '<span class="fw-bold ' + (r.result == 1 ? 'text-success' : 'text-danger') + '">' + r.msg + '</span>', icon: r.result == 1 ? "success" : "error", showConfirmButton: true });
                },
                error: function (j, e) { Swal.close(); ShowErrorAjax(j, e); }
            });
        });
    }

    function ComingSoon() {
        Swal.fire({ title: "แจ้งเตือน", html: '<span class="text-secondary">ฟังก์ชันนี้ยังไม่เปิดใช้งาน (รอเชื่อมระบบ e-Tax)</span>', icon: "info", confirmButtonText: "ตกลง" });
    }
</script>
