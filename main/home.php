<?php $breadcrumbs = [['label' => 'หน้าแรก']]; ?>
<?php include "header.php"; ?>

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
                                ภาพรวมข้อมูล ตั้งแต่ <span id="DashDateFrom">01/06/2026</span> ถึง <span id="DashDateTo">25/06/2026</span>
                            </p>
                        </div>
                        <div class="position-relative">
                            <button type="button" class="btn d-inline-flex align-items-center gap-1" id="DashDateBtn"
                                style="background:#ffffff;color:#605DFF;border:0;font-weight:500;box-shadow:0 2px 6px rgba(16,24,40,.12);">
                                <span class="material-symbols-outlined" style="font-size: 18px;">calendar_month</span>
                                เลือกวันที่
                            </button>
                            <!-- input ซ่อนไว้สำหรับ flatpickr range (ปุ่มด้านบนเป็นตัวเปิด) -->
                            <input type="text" id="DashDateRange" class="position-absolute end-0 top-100"
                                style="width:1px;height:1px;opacity:0;pointer-events:none;border:0;padding:0;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- การ์ดสถิติ (ข้อมูล mock — รอต่อ API จริงภายหลัง) -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card bg-white border-0 rounded-3 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-secondary fs-14 mb-2">สมาชิกใหม่</p>
                                    <h3 class="mb-0"><span id="StatNewMembers">93</span> <small class="fs-14 fw-normal text-secondary">คน</small></h3>
                                </div>
                                <div class="stat-icon" style="background:#eef0ff; color:#605DFF;">
                                    <span class="material-symbols-outlined">group</span>
                                </div>
                            </div>
                            <span class="stat-trend up mt-3"><span class="material-symbols-outlined" style="font-size:16px;">trending_up</span> +12% จากเดือนก่อน</span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card bg-white border-0 rounded-3 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-secondary fs-14 mb-2">คำสั่งซื้อใหม่</p>
                                    <h3 class="mb-0"><span id="StatNewOrders">105</span> <small class="fs-14 fw-normal text-secondary">รายการ</small></h3>
                                </div>
                                <div class="stat-icon" style="background:#e8f8ef; color:#16a34a;">
                                    <span class="material-symbols-outlined">shopping_cart</span>
                                </div>
                            </div>
                            <span class="stat-trend up mt-3"><span class="material-symbols-outlined" style="font-size:16px;">trending_up</span> +8% จากเดือนก่อน</span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card bg-white border-0 rounded-3 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-secondary fs-14 mb-2">คำสั่งซื้อใหม่ (ยอดเงิน)</p>
                                    <h3 class="mb-0"><span id="StatNewRevenue">45,901</span> <small class="fs-14 fw-normal text-secondary">฿</small></h3>
                                </div>
                                <div class="stat-icon" style="background:#fff4e5; color:#f59e0b;">
                                    <span class="material-symbols-outlined">payments</span>
                                </div>
                            </div>
                            <span class="stat-trend up mt-3"><span class="material-symbols-outlined" style="font-size:16px;">trending_up</span> +5% จากเดือนก่อน</span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card bg-white border-0 rounded-3 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-secondary fs-14 mb-2">ยอดเงิน OTP คงเหลือ</p>
                                    <h3 class="mb-0"><span id="StatOtpBalance">24</span> <small class="fs-14 fw-normal text-secondary">USD</small></h3>
                                </div>
                                <div class="stat-icon" style="background:#e6f6fb; color:#0ea5e9;">
                                    <span class="material-symbols-outlined">sms</span>
                                </div>
                            </div>
                            <span class="stat-trend down mt-3"><span class="material-symbols-outlined" style="font-size:16px;">trending_down</span> เหลือน้อย ควรเติม</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- กราฟยอดขายรายวัน (ข้อมูล mock) -->
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
    $(document).ready(function () {

        /* ===== กราฟยอดขายรายวัน (mock) =====
           ข้อมูลจริงให้แทนที่ DASH_DAYS / DASH_SALES ด้วยผลจาก API ภายหลัง */
        var DASH_DAYS = [
            "Jun '26", "02 Jun", "03 Jun", "04 Jun", "05 Jun", "06 Jun", "07 Jun",
            "08 Jun", "09 Jun", "10 Jun", "11 Jun", "12 Jun", "13 Jun", "14 Jun",
            "15 Jun", "16 Jun", "17 Jun", "18 Jun", "19 Jun", "20 Jun", "21 Jun",
            "22 Jun", "23 Jun", "24 Jun"
        ];
        var DASH_SALES = [
            400, 2000, 2400, 850, 1600, 1150, 450, 1300, 2400, 800, 50, 3200,
            1600, 2800, 2800, 3200, 3200, 1750, 800, 3200, 1550, 1100, 400, 3600
        ];

        if (typeof ApexCharts !== "undefined" && document.getElementById("DashSalesChart")) {
            var salesChart = new ApexCharts(document.getElementById("DashSalesChart"), {
                chart: { type: "area", height: 360, fontFamily: "'Kanit', sans-serif", toolbar: { show: false }, zoom: { enabled: false } },
                series: [{ name: "ยอดขาย", data: DASH_SALES }],
                colors: ["#605DFF"],
                stroke: { curve: "smooth", width: 3 },
                fill: {
                    type: "gradient",
                    gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 90, 100] }
                },
                grid: { borderColor: "#eef0f3", strokeDashArray: 4, padding: { left: 8, right: 8 } },
                dataLabels: { enabled: false },
                markers: { size: 0, hover: { size: 5 } },
                xaxis: {
                    categories: DASH_DAYS,
                    tickPlacement: "on",
                    labels: { rotate: -45, rotateAlways: false, hideOverlappingLabels: true, style: { fontSize: "12px", colors: "#94a3b8" } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    min: 0,
                    tickAmount: 4,
                    labels: { style: { colors: "#94a3b8" }, formatter: function (v) { return (typeof NumberFormat === "function") ? NumberFormat(Math.round(v)) : Math.round(v); } }
                },
                tooltip: { y: { formatter: function (v) { return ((typeof NumberFormat === "function") ? NumberFormat(v) : v) + " ฿"; } } }
            });
            salesChart.render();
        }

        /* ===== ปุ่มเลือกช่วงวันที่ (flatpickr range) — อัปเดตข้อความช่วงวันที่ด้านบน ===== */
        if (typeof flatpickr !== "undefined") {
            var fp = flatpickr("#DashDateRange", {
                mode: "range",
                dateFormat: "d/m/Y",
                defaultDate: ["2026-06-01", "2026-06-25"],
                onClose: function (selectedDates) {
                    if (selectedDates.length === 2) {
                        $("#DashDateFrom").text(fp.formatDate(selectedDates[0], "d/m/Y"));
                        $("#DashDateTo").text(fp.formatDate(selectedDates[1], "d/m/Y"));
                        // TODO: ยิง API โหลดสถิติ/กราฟตามช่วงวันที่ใหม่
                    }
                }
            });
            $("#DashDateBtn").on("click", function () { fp.open(); });
        }
    });
</script>
