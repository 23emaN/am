/**
 * loading.js
 * -----------------------------------------------------------------------------
 * โหลดแบบ spinner หมุนอยู่กลางจอ (full-screen) — ใช้เหมือนกันทั้ง backoffice
 * โผล่อัตโนมัติทุก ajax ของ jQuery (รวม DataTables serverSide) แบบมี delay
 * โชว์ทันที + ค้างอย่างน้อย MIN_SHOW ms (กันกระพริบ/ให้ทันเห็นบนเครื่อง local ที่โหลดเร็ว)
 * override ShowLoadingOverlay/HideLoadingOverlay จาก main.js (โหลดไฟล์นี้หลัง main.js)
 */
(function () {
    "use strict";

    var MIN_SHOW = 350;       // ms: โชว์แล้วค้างอย่างน้อยเท่านี้ กันกระพริบ + ให้ทันเห็น
    var active = 0;           // นับจำนวน request ที่กำลังโหลด (รองรับซ้อนกัน)
    var shownAt = 0;          // เวลาที่เริ่มโชว์ spinner (ms)
    var hideTimer = null;
    var watchdog = null;      // กันค้างถาวร ถ้า Show/Hide ไม่คู่กัน
    var $overlay = null;

    function overlay() {
        if (!$overlay) {
            $overlay = $(
                '<div id="cpdth-loading">' +
                    '<div class="cpdth-loading-spinner spinner-border" role="status">' +
                        '<span class="visually-hidden">กำลังโหลด...</span>' +
                    '</div>' +
                '</div>'
            ).appendTo('body');
        }
        return $overlay;
    }

    function reallyShow() {
        // ถ้าหน้าไหนเปิด SweetAlert (เช่น loader ของตัวเอง) ค้างอยู่แล้ว -> ไม่ต้องซ้อน spinner
        if (window.Swal && typeof Swal.isVisible === "function" && Swal.isVisible()) { return; }
        if (hideTimer !== null) { clearTimeout(hideTimer); hideTimer = null; }
        shownAt = Date.now();
        overlay().addClass('show');
    }

    function reallyHide() {
        if ($overlay) { $overlay.removeClass('show'); }
    }

    function show() {
        active++;
        if (active === 1) { reallyShow(); }
        // watchdog กันค้างถาวร: ถ้าไม่มี hide ภายใน 15 วิ บังคับปิด (รีเซ็ตทุกครั้งที่ show)
        if (watchdog) { clearTimeout(watchdog); }
        watchdog = setTimeout(function () { active = 0; watchdog = null; reallyHide(); }, 15000);
    }

    function hide() {
        active = Math.max(0, active - 1);
        if (active !== 0) { return; }
        if (watchdog) { clearTimeout(watchdog); watchdog = null; }
        // ค้าง spinner ให้ครบ MIN_SHOW ก่อนซ่อน (ถ้าโหลดเสร็จเร็วกว่านั้น)
        var wait = Math.max(0, MIN_SHOW - (Date.now() - shownAt));
        if (hideTimer !== null) { clearTimeout(hideTimer); }
        hideTimer = setTimeout(function () {
            hideTimer = null;
            if (active === 0) { reallyHide(); }
        }, wait);
    }

    // โชว์ spinner อัตโนมัติทุก ajax ของ jQuery (รวม DataTables) — ไม่ต้องแก้ทีละหน้า
    $(document).ajaxStart(show);
    $(document).ajaxStop(hide);

    // helper เดิม -> ชี้มาที่ spinner กลางจอ (เดิมเป็น section overlay / top-bar)
    window.ShowLoadingOverlay = function () { show(); };
    window.HideLoadingOverlay = function () { hide(); };

    // ปุ่ม: เอาวงกลมหมุนออก เหลือแค่ disable + หรี่จาง
    window.ShowLoadingButton = function (selector) {
        var $el = $(selector);
        if ($el.is('button') || $el.attr('type') === 'submit') {
            $el.prop('disabled', true);
        }
        $el.addClass('cpdth-btn-loading');
    };

    window.HideLoadingButton = function (selector) {
        var $el = $(selector);
        if ($el.is('button') || $el.attr('type') === 'submit') {
            $el.prop('disabled', false);
        }
        $el.removeClass('cpdth-btn-loading');
    };
})();

/**
 * ลดเวลาแจ้งเตือน SweetAlert ให้เด้งเร็วขึ้น
 * เดิมหลายไฟล์ตั้ง timer: 2000 (2 วินาที) ทำให้รอนาน
 * patch รวมศูนย์ที่เดียว: ครอบ Swal.fire ทุกจุดที่มี timer ให้ไม่เกินเพดานนี้
 * (ไดอะล็อกยืนยัน/ที่ไม่มี timer ไม่ได้รับผลกระทบ)
 */
(function () {
    "use strict";

    var MAX_TIMER = 800; // ms: เพดานเวลา auto-close ของ toast

    if (typeof window.Swal === "undefined" ||
        typeof Swal.fire !== "function" ||
        Swal.__cpdthTimerPatched) {
        return;
    }

    var origFire = Swal.fire;
    Swal.fire = function () {
        var opt = arguments[0];
        if (opt && typeof opt === "object" && typeof opt.timer === "number" && opt.timer > MAX_TIMER) {
            opt.timer = MAX_TIMER;
        }
        return origFire.apply(Swal, arguments);
    };
    Swal.__cpdthTimerPatched = true;
})();

/**
 * DataTables: จัดการข้อความในตาราง (ใช้ spinner กลางจอเป็นตัวบอกโหลดแทน)
 * ----------------------------------------------------------------------------
 * 1) preInit.dt (ก่อนวาดตารางครั้งแรก): ล้างข้อความ sLoadingRecords เป็นค่าว่าง
 *    -> ตอนกำลังโหลดครั้งแรก tbody จะว่าง ไม่โชว์ "กำลังโหลดข้อมูล..." (spinner กลางจอทำหน้าที่แทน)
 * 2) xhr.dt (หลังได้ข้อมูลแต่ "ก่อน" วาดตาราง): ตั้ง sLoadingRecords = sEmptyTable
 *    -> แก้บั๊ก DataTables ที่ตาราง serverSide draw แรก (iDraw==1) ถ้าผลว่างจะค้างข้อความ
 *    loadingRecords แทน emptyTable -> ให้โชว์ "ไม่มีข้อมูลในตาราง" ถูกต้อง
 */
(function () {
    "use strict";
    if (typeof $ === "undefined") { return; }
    $(document).on('preInit.dt', function (e, settings) {
        if (settings && settings.oLanguage) {
            settings.oLanguage.sLoadingRecords = '';
        }
    });
    $(document).on('xhr.dt', function (e, settings) {
        if (settings && settings.oLanguage) {
            settings.oLanguage.sLoadingRecords = settings.oLanguage.sEmptyTable;
        }
    });
})();

/**
 * แก้บั๊ก theme menu (sidebar-menu.js): Menu.manageScroll() เรียก PerfectScrollbar
 * (this._scrollbar.destroy() / new PerfectScrollbar) ตอน window resize
 * แต่ sidebar เราใช้ SimpleBar (data-simplebar) ไม่มี PerfectScrollbar -> _scrollbar = undefined
 * และเช็ก `!== null` ดักไม่ได้ undefined -> Uncaught TypeError ตอน resize จอแคบ
 * ครอบ try/catch กัน error (ไม่กระทบการเลื่อนเมนู เพราะ SimpleBar จัดการ scroll เอง)
 */
(function () {
    "use strict";
    if (typeof window.Menu === "function" &&
        window.Menu.prototype &&
        typeof window.Menu.prototype.manageScroll === "function" &&
        !window.Menu.prototype.__cpdthScrollPatched) {
        var origManageScroll = window.Menu.prototype.manageScroll;
        window.Menu.prototype.manageScroll = function () {
            try {
                return origManageScroll.apply(this, arguments);
            } catch (e) {
                /* theme PerfectScrollbar/SimpleBar resize bug — ปล่อยผ่าน */
            }
        };
        window.Menu.prototype.__cpdthScrollPatched = true;
    }
})();
