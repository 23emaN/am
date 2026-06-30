<?php $breadcrumbs = [['label' => 'ลิ้งค์ออกใบกำกับภาษี (E-Tax)']]; ?>
<?php include "header.php"; ?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <?php include "navbar.php"; ?>
        <div class="px-2">
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-3 p-4">
                    <h4 class="mb-0">ลิ้งค์ออกใบกำกับภาษี (E-Tax)</h4>
                    <a href="etax_link_fromadd" class="btn btn-success d-inline-flex align-items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size:18px;">add</span> สร้างลิ้งค์ใหม่
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="default-table-area">
                        <div class="table-responsive">
                            <table class="table align-middle w-100" id="PageTable">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center" style="width:60px;">ID</th>
                                        <th scope="col">เลขใบกำกับ</th>
                                        <th scope="col">ลูกค้า</th>
                                        <th scope="col">รายการ</th>
                                        <th scope="col" class="text-nowrap">วันที่ในใบกำกับ</th>
                                        <th scope="col" class="text-center">สถานะเอกสาร</th>
                                        <th scope="col" class="text-center">สถานะลิงค์</th>
                                        <th scope="col" class="text-center" style="width:200px;">ดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody id="EtaxLinkBody"></tbody>
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
    var pageTable = null;

    function publicLinkUrl(token) {
        var base = location.href.split('/main/')[0];
        return base + '/etax_link_pdf.php?token=' + token;
    }

    function docStatusBadge(s) {
        if (s === "2") { return '<span class="badge bg-danger">ยกเลิก</span>'; }
        return '<span class="badge bg-success">ออกใบกำกับภาษีแล้ว</span>';
    }
    function linkStatusBadge(s) {
        if (s === "0") { return '<span class="badge bg-secondary">ปิดใช้งาน</span>'; }
        return '<span class="badge bg-success">ใช้งานได้</span>';
    }

    $(document).ready(function () { LoadData(); });

    function LoadData() {
        $.ajax({
            beforeSend: function () { ShowLoadingOverlay("#PageTable"); },
            type: "POST", url: "core.php",
            data: { request_state: "list_etax_link", request_function: "get_list" },
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

    function RenderTable(list) {
        if (pageTable) { pageTable.destroy(); pageTable = null; }
        var rows = "";
        (list || []).forEach(function (it) {
            var sq = 'btn btn-sm d-inline-flex align-items-center justify-content-center p-0';
            var linkOn = it.link_status === "1";
            var actions = '<div class="d-flex justify-content-center gap-1">' +
                '<a href="etax_link_view.php?id=' + it.id + '" class="' + sq + ' btn-info text-white" style="width:34px;height:34px;" title="ดูข้อมูล">' +
                    '<span class="material-symbols-outlined" style="font-size:18px;">visibility</span></a>' +
                '<button type="button" class="' + sq + ' btn-secondary" style="width:34px;height:34px;" onclick="CopyLink(\'' + it.token + '\')" title="คัดลอกลิ้งค์">' +
                    '<span class="material-symbols-outlined" style="font-size:18px;">link</span></button>' +
                '<button type="button" class="' + sq + ' btn-success" style="width:34px;height:34px;" onclick="DownloadEtaxLink(' + it.id + ')" title="ดาวน์โหลด PDF">' +
                    '<span class="material-symbols-outlined" style="font-size:18px;">download</span></button>' +
                '<button type="button" class="' + sq + (linkOn ? ' btn-warning' : ' btn-outline-secondary') + '" style="width:34px;height:34px;" onclick="ToggleLink(' + it.id + ')" title="' + (linkOn ? 'ปิดใช้งานลิ้งค์' : 'เปิดใช้งานลิ้งค์') + '">' +
                    '<span class="material-symbols-outlined" style="font-size:18px;">' + (linkOn ? 'link_off' : 'link') + '</span></button>' +
                '<button type="button" class="' + sq + ' btn-danger" style="width:34px;height:34px;" onclick="DeleteLink(' + it.id + ')" title="ลบ">' +
                    '<span class="material-symbols-outlined" style="font-size:18px;">delete</span></button>' +
                '</div>';
            rows +=
                '<tr>' +
                    '<td class="text-center">' + it.id + '</td>' +
                    '<td class="fw-medium">' + EscapeHTML(it.etax_no) + '</td>' +
                    '<td>' + EscapeHTML(it.customer) + '</td>' +
                    '<td class="text-secondary">' + EscapeHTML(it.items) + '</td>' +
                    '<td class="text-nowrap">' + EscapeHTML(it.date) + '</td>' +
                    '<td class="text-center">' + docStatusBadge(it.doc_status) + '</td>' +
                    '<td class="text-center">' + linkStatusBadge(it.link_status) + '</td>' +
                    '<td class="text-center">' + actions + '</td>' +
                '</tr>';
        });
        $("#EtaxLinkBody").html(rows);
        pageTable = $("#PageTable").DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [[0, 'desc']],
            language: { url: '../template/assets/js/data-table-th.json' },
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "ทั้งหมด"]],
            columnDefs: [{ orderable: false, targets: [3, 7] }]
        });
    }

    function CopyLink(token) {
        var url = publicLinkUrl(token);
        var done = function () {
            Swal.fire({ toast: true, position: "top-end", icon: "success", title: "คัดลอกลิ้งค์แล้ว", showConfirmButton: false, timer: 1500 });
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(done).catch(function () { window.prompt("คัดลอกลิ้งค์:", url); });
        } else {
            window.prompt("คัดลอกลิ้งค์:", url);
        }
    }

    function DownloadEtaxLink(id) {
        $.ajax({
            type: "POST", url: "core.php",
            data: { request_state: "list_etax_link", request_function: "get", link_id: id },
            dataType: "json",
            success: function (r) {
                if (r.result != 1) { Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + (r.msg || "ไม่พบข้อมูล") + '</span>', icon: "error" }); return; }
                var taxId = ((r.data.link && r.data.link.tax_id) || "").replace(/\D/g, "");
                var pass = taxId.length >= 4 ? taxId.slice(-4) : taxId;
                Swal.fire({
                    icon: "success", title: "ดาวน์โหลดใบกำกับภาษี",
                    html: 'รหัสเปิดไฟล์ใบกำกับภาษีของคุณคือ <b style="font-size:1.3em;">' + (pass || "-") + '</b>',
                    confirmButtonText: "ดาวน์โหลด", confirmButtonColor: "#605DFF"
                }).then(function (res) {
                    if (res.isConfirmed) { window.open("pdf_preview.php?type=etaxlink&id=" + id, "_blank"); }
                });
            },
            error: function (j, e) { ShowErrorAjax(j, e); }
        });
    }

    function ToggleLink(id) {
        $.ajax({
            type: "POST", url: "core.php",
            data: { request_state: "list_etax_link", request_function: "toggle_link", link_id: id },
            dataType: "json",
            success: function (res) {
                if (res.result == 1) {
                    Swal.fire({ toast: true, position: "top-end", icon: "success", title: res.msg, showConfirmButton: false, timer: 1400 });
                    LoadData();
                } else {
                    Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + res.msg + '</span>', icon: "error", showConfirmButton: true });
                }
            },
            error: function (j, e) { ShowErrorAjax(j, e); }
        });
    }

    function DeleteLink(id) {
        Swal.fire({
            title: "ยืนยันการลบ",
            html: '<span class="text-secondary">ต้องการลบลิ้งค์ใบกำกับภาษีนี้ใช่หรือไม่?</span>',
            icon: "warning", showCancelButton: true, confirmButtonText: "ลบ", cancelButtonText: "ปิด", confirmButtonColor: "#dc3545"
        }).then(function (result) {
            if (!result.isConfirmed) { return; }
            $.ajax({
                type: "POST", url: "core.php",
                data: { request_state: "list_etax_link", request_function: "delete", link_id: id },
                dataType: "json",
                success: function (res) {
                    if (res.result == 1) {
                        Swal.fire({ title: "สำเร็จ", html: '<span class="fw-bold text-success">' + res.msg + '</span>', icon: "success", showConfirmButton: false, timer: 1400 }).then(function () { LoadData(); });
                    } else {
                        Swal.fire({ title: "แจ้งเตือน", html: '<span class="fw-bold text-danger">' + res.msg + '</span>', icon: "error", showConfirmButton: true });
                    }
                },
                error: function (j, e) { ShowErrorAjax(j, e); }
            });
        });
    }
</script>
