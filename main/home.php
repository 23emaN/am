<?php
    $breadcrumbs = [['label' => 'หน้าแรก']];
    // ช่วงวันที่เริ่มต้น = 30 วันล่าสุด
    $dash_from_ymd  = date('Y-m-d', strtotime('-29 days'));
    $dash_to_ymd    = date('Y-m-d');
    $dash_from_disp = date('d/m/Y', strtotime('-29 days'));
    $dash_to_disp   = date('d/m/Y');
?>
<?php include "header.php"; ?>

<style>
    /* ไอคอน info บนหัวการ์ดสถิติ: จาง ๆ ไม่แย่งสายตา แต่ hover แล้วชัด */
    .stat-info { color: var(--text-muted); cursor: help; line-height: 1; opacity: .7; transition: opacity .15s ease; }
    .stat-info:hover, .stat-info:focus { opacity: 1; outline: none; }
    .stat-info .material-symbols-outlined { font-size: 16px; vertical-align: middle; }
    /* ข้อความบอกช่วงก่อนหน้าที่ใช้เทียบ */
    .stat-compare { font-size: 11px; line-height: 1.3; }
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <?php include "navbar.php"; ?>

        <div class="px-2">

            <!-- การ์ดต้อนรับ + ช่วงวันที่ + ปุ่มเลือกวันที่ -->
            <div class="card bg-primary border-0 rounded-3 welcome-box mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h3 class="text-white fw-semibold mb-1">ยินดีต้อนรับ <span class="ShowUserFullname">Admin</span></h3>
                            <p class="text-light mb-0">
                                ภาพรวมข้อมูล ตั้งแต่ <span id="DashDateFrom"><?php echo $dash_from_disp; ?></span> ถึง <span id="DashDateTo"><?php echo $dash_to_disp; ?></span>
                            </p>
                        </div>
                        <div class="position-relative">
                            <button type="button" class="btn welcome-date-btn d-inline-flex align-items-center gap-1" id="DashDateBtn">
                                <span class="material-symbols-outlined" aria-hidden="true">calendar_month</span>
                                เลือกวันที่
                            </button>
                            <!-- input ซ่อนไว้สำหรับ flatpickr range (ปุ่มด้านบนเป็นตัวเปิด) -->
                            <input type="text" id="DashDateRange" class="position-absolute end-0 top-100" aria-label="เลือกช่วงวันที่ของข้อมูลแดชบอร์ด"
                                style="width:1px;height:1px;opacity:0;pointer-events:none;border:0;padding:0;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- การ์ดสถิติ (ตัวเลข + แนวโน้มเทียบช่วงก่อนหน้า โหลดจาก API จริง) -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card bg-white border-0 rounded-3 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-secondary fs-14 mb-2 d-inline-flex align-items-center gap-1">สมาชิกใหม่
                                        <span class="stat-info" tabindex="0" role="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                              title="นับจำนวนสมาชิกที่สมัครใหม่ (ตามวันที่สมัคร) ภายในช่วงที่เลือก ไม่รวมบัญชีที่ถูกลบ">
                                            <span class="material-symbols-outlined" aria-hidden="true">info</span>
                                        </span>
                                    </p>
                                    <h3 class="mb-0"><span id="StatNewMembers" class="stat-value">–</span> <small class="fs-14 fw-normal text-secondary">คน</small></h3>
                                </div>
                                <div class="stat-icon stat-icon--brand">
                                    <span class="material-symbols-outlined" aria-hidden="true">group</span>
                                </div>
                            </div>
                            <span class="stat-trend mt-3" id="TrendMembers"></span>
                            <span class="stat-compare text-secondary d-block mt-1" id="CompareMembers"></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card bg-white border-0 rounded-3 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-secondary fs-14 mb-2 d-inline-flex align-items-center gap-1">คำสั่งซื้อใหม่
                                        <span class="stat-info" tabindex="0" role="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                              title="นับจำนวนคำสั่งซื้อทั้งหมดที่สร้างภายในช่วงที่เลือก (รวมทุกสถานะการชำระเงิน)">
                                            <span class="material-symbols-outlined" aria-hidden="true">info</span>
                                        </span>
                                    </p>
                                    <h3 class="mb-0"><span id="StatNewOrders" class="stat-value">–</span> <small class="fs-14 fw-normal text-secondary">รายการ</small></h3>
                                </div>
                                <div class="stat-icon stat-icon--success">
                                    <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
                                </div>
                            </div>
                            <span class="stat-trend mt-3" id="TrendOrders"></span>
                            <span class="stat-compare text-secondary d-block mt-1" id="CompareOrders"></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card bg-white border-0 rounded-3 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-secondary fs-14 mb-2 d-inline-flex align-items-center gap-1">คำสั่งซื้อใหม่ (ยอดเงิน)
                                        <span class="stat-info" tabindex="0" role="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                              title="ผลรวมยอดเงินของคำสั่งซื้อที่ชำระเงินแล้ว ภายในช่วงที่เลือก">
                                            <span class="material-symbols-outlined" aria-hidden="true">info</span>
                                        </span>
                                    </p>
                                    <h3 class="mb-0"><span id="StatNewRevenue" class="stat-value">–</span> <small class="fs-14 fw-normal text-secondary">฿</small></h3>
                                </div>
                                <div class="stat-icon stat-icon--warning">
                                    <span class="material-symbols-outlined" aria-hidden="true">payments</span>
                                </div>
                            </div>
                            <span class="stat-trend mt-3" id="TrendRevenue"></span>
                            <span class="stat-compare text-secondary d-block mt-1" id="CompareRevenue"></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card bg-white border-0 rounded-3 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-secondary fs-14 mb-2 d-inline-flex align-items-center gap-1">ยอดเงิน OTP คงเหลือ
                                        <span class="stat-info" tabindex="0" role="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                              title="ยอดเงินคงเหลือสำหรับส่งรหัส OTP ยืนยันตัวตน · อยู่ระหว่างเชื่อมต่อ API ผู้ให้บริการ OTP (ยังไม่มีข้อมูลจริง)">
                                            <span class="material-symbols-outlined" aria-hidden="true">info</span>
                                        </span>
                                    </p>
                                    <h3 class="mb-0"><span id="StatOtpBalance" class="stat-value text-secondary">—</span> <small class="fs-14 fw-normal text-secondary">USD</small></h3>
                                </div>
                                <div class="stat-icon stat-icon--info">
                                    <span class="material-symbols-outlined" aria-hidden="true">sms</span>
                                </div>
                            </div>
                            <span class="empty-state mt-3" style="font-size:12px;"><span class="material-symbols-outlined" style="font-size:16px;" aria-hidden="true">info</span> รอเชื่อม API ผู้ให้บริการ OTP</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- กราฟยอดขายรายวัน (ข้อมูลจริงจาก get_dashboard) -->
            <div class="card bg-white border-0 rounded-3 mb-4">
                <div class="card-header bg-white border-0 p-4 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-1">ยอดขายแยกเป็นรายวัน</h5>
                        <p class="text-secondary fs-14 mb-0">สรุปยอดขายเฉพาะคำสั่งซื้อที่สำเร็จแล้วแยกเป็นรายวัน</p>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary fs-12 px-3 py-2 rounded-pill">หน่วย: บาท (฿)</span>
                </div>
                <div class="card-body p-4">
                    <div id="DashSalesChart"></div>
                </div>
            </div>

        </div>

        <?php include "footer.php"; ?>
    </div>
</div>

<div class="modal fade" id="mainModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 1200px;">
        <div class="modal-content animated fadeIn" id="LoadingMainModal">
            <div id="showMainModal"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content animated fadeIn" id="LoadingMyModal">
            <div id="showModal"></div>
        </div>
    </div>
</div>

<div class="modal" id="subModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content animated fadeIn" id="LoadingSubModal">
            <div id="showSubModal"></div>
        </div>
    </div>
</div>

<?php include "script.php"; ?>

</body>

</html>

<script>
    var salesChart = null;

    // สร้าง/อัปเดตกราฟยอดขายรายวัน
    function RenderSalesChart(days, sales) {
        if (typeof ApexCharts === "undefined" || !document.getElementById("DashSalesChart")) { return; }
        if (salesChart) {
            salesChart.updateOptions({ series: [{ name: "ยอดขาย", data: sales }], xaxis: { categories: days } });
            return;
        }
        salesChart = new ApexCharts(document.getElementById("DashSalesChart"), {
            chart: { type: "area", height: 360, fontFamily: "'Kanit', sans-serif", toolbar: { show: false }, zoom: { enabled: false } },
            series: [{ name: "ยอดขาย", data: sales }],
            colors: ["#605DFF"],
            stroke: { curve: "smooth", width: 3 },
            fill: { type: "gradient", gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 90, 100] } },
            grid: { borderColor: "#eef0f3", strokeDashArray: 4, padding: { left: 8, right: 8 } },
            dataLabels: { enabled: false },
            markers: { size: 0, hover: { size: 5 } },
            xaxis: {
                categories: days,
                tickPlacement: "on",
                labels: { rotate: -45, rotateAlways: false, hideOverlappingLabels: true, style: { fontSize: "12px", colors: "#64748b" } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                min: 0,
                tickAmount: 4,
                labels: { style: { colors: "#64748b" }, formatter: function (v) { return (typeof NumberFormat === "function") ? NumberFormat(Math.round(v)) : Math.round(v); } }
            },
            tooltip: { y: { formatter: function (v) { return ((typeof NumberFormat === "function") ? NumberFormat(v) : v) + " ฿"; } } }
        });
        salesChart.render();
    }

    // แสดงป้ายแนวโน้มเทียบช่วงก่อนหน้า (ข้อมูลจริงจาก API)
    function RenderTrend(elId, t) {
        var el = document.getElementById(elId);
        if (!el) { return; }
        if (!t || t.dir === "flat") {
            el.className = "stat-trend mt-3 text-secondary";
            el.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">trending_flat</span> เท่ากับช่วงก่อน';
            return;
        }
        var up = t.dir === "up";
        el.className = "stat-trend mt-3 " + (up ? "up" : "down");
        var icon = up ? "trending_up" : "trending_down";
        var nf = function (v) { return (typeof NumberFormat === "function") ? NumberFormat(v) : v; };
        var pctTxt;
        if (t.pct === null || typeof t.pct === "undefined") {
            // ช่วงก่อนหน้าไม่มีฐานเทียบ (=0) -> โชว์จำนวนที่เพิ่ม/ลดจริงแทน %
            pctTxt = (up ? "+" : "-") + nf(Math.round(Math.abs(t.diff || 0)));
        } else {
            pctTxt = (up ? "+" : "-") + t.pct + "%";
        }
        el.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">' + icon + '</span> ' + pctTxt + ' จากช่วงก่อน';
    }

    // แสดงช่วงก่อนหน้าที่ใช้เป็นฐานเทียบแนวโน้ม (ให้ผู้ใช้รู้ว่า "จากช่วงก่อน" คือช่วงไหน)
    function RenderCompare(period) {
        var txt = "";
        if (period && period.prev_from && period.prev_to) {
            txt = 'เทียบกับช่วงก่อน ' + period.prev_from + ' – ' + period.prev_to;
        }
        ["CompareMembers", "CompareOrders", "CompareRevenue"].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) { el.textContent = txt; }
        });
    }

    // เปิดใช้งาน Bootstrap tooltip สำหรับไอคอน info บนการ์ดสถิติ
    function InitStatTooltips() {
        if (typeof bootstrap === "undefined" || !bootstrap.Tooltip) { return; }
        document.querySelectorAll('.stat-info[data-bs-toggle="tooltip"]').forEach(function (el) {
            if (!bootstrap.Tooltip.getInstance(el)) { new bootstrap.Tooltip(el); }
        });
    }

    // โหลดข้อมูลจริงตามช่วงวันที่ (อ่านจากข้อความช่วงวันที่ด้านบน)
    function LoadDashboard() {
        var nf = function (v) { return (typeof NumberFormat === "function") ? NumberFormat(v) : v; };
        $.ajax({
            type: "POST", url: "core.php",
            data: {
                request_state: "dashboard",
                request_function: "get_dashboard",
                date_from: $("#DashDateFrom").text(),
                date_to: $("#DashDateTo").text()
            },
            dataType: "json",
            success: function (r) {
                if (r.result != 1) { return; }
                var d = r.data;
                $("#StatNewMembers").text(nf(d.new_members));
                $("#StatNewOrders").text(nf(d.new_orders));
                $("#StatNewRevenue").text(nf(Math.round(d.revenue)));
                var tr = d.trend || {};
                RenderTrend("TrendMembers", tr.members);
                RenderTrend("TrendOrders", tr.orders);
                RenderTrend("TrendRevenue", tr.revenue);
                RenderCompare(d.period);
                RenderSalesChart(d.days || [], d.sales || []);
            },
            error: function (j, e) { if (typeof ShowErrorAjax === "function") { ShowErrorAjax(j, e); } }
        });
    }

    $(document).ready(function () {
        /* ===== ปุ่มเลือกช่วงวันที่ (flatpickr range) -> โหลดข้อมูลใหม่ ===== */
        if (typeof flatpickr !== "undefined") {
            var fp = flatpickr("#DashDateRange", {
                mode: "range",
                dateFormat: "d/m/Y",
                defaultDate: ["<?php echo $dash_from_ymd; ?>", "<?php echo $dash_to_ymd; ?>"],
                onClose: function (selectedDates) {
                    if (selectedDates.length === 2) {
                        $("#DashDateFrom").text(fp.formatDate(selectedDates[0], "d/m/Y"));
                        $("#DashDateTo").text(fp.formatDate(selectedDates[1], "d/m/Y"));
                        LoadDashboard();
                    }
                }
            });
            $("#DashDateBtn").on("click", function () { fp.open(); });
        }

        InitStatTooltips();
        LoadDashboard();
    });
</script>
