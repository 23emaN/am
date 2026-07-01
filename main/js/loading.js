/**
 * loading.js
 * -----------------------------------------------------------------------------
 * Loading กลางหน้าจอแบบ spinner หมุน (full-screen centered spinner)
 * override ShowLoadingOverlay/HideLoadingOverlay จาก main.js (โหลดไฟล์นี้หลัง main.js)
 * - โชว์ทันที (เห็นชัด) + fade เล็กน้อยกันกระพริบ
 * - นับ request ซ้อนกันด้วยตัวนับ active (Show/Hide ต้องคู่กันเสมอ)
 * - ละเลย argument selector เดิม (เป็น overlay เต็มจอ)
 */
(function () {
    "use strict";

    var active = 0;
    var $ov = null;
    var hideTimer = null;
    var watchdog = null;

    function ensureStyle() {
        if (document.getElementById('cpdth-spin-style')) { return; }
        var css =
            '@keyframes cpdth-spin{to{transform:rotate(360deg)}}' +
            '#cpdth-loading-overlay{position:fixed;inset:0;z-index:99999;display:none;' +
            'align-items:center;justify-content:center;background:rgba(255,255,255,.55);' +
            'opacity:0;transition:opacity .15s ease;}' +
            '#cpdth-loading-overlay.show{opacity:1;}' +
            '#cpdth-loading-overlay .cpdth-spinner{width:56px;height:56px;border-radius:50%;' +
            'border:5px solid rgba(96,93,255,.22);border-top-color:#605DFF;' +
            'animation:cpdth-spin .7s linear infinite;}';
        var s = document.createElement('style');
        s.id = 'cpdth-spin-style';
        s.appendChild(document.createTextNode(css));
        document.head.appendChild(s);
    }

    function overlay() {
        if (!$ov) {
            ensureStyle();
            $ov = $('<div id="cpdth-loading-overlay"><div class="cpdth-spinner"></div></div>').appendTo('body');
        }
        return $ov;
    }

    function doHide() {
        if (!$ov) { return; }
        $ov.removeClass('show');
        if (hideTimer) { clearTimeout(hideTimer); }
        hideTimer = setTimeout(function () {
            if (active === 0 && $ov) { $ov.css('display', 'none'); }
            hideTimer = null;
        }, 160);
    }

    window.ShowLoadingOverlay = function () {
        active++;
        if (active === 1) {
            if (hideTimer) { clearTimeout(hideTimer); hideTimer = null; }
            var $o = overlay();
            $o.css('display', 'flex');
            $o[0].offsetWidth; // reflow ก่อน fade-in
            $o.addClass('show');
        }
        // watchdog กันค้างถาวร: ถ้าไม่มี HideLoadingOverlay มาภายใน 12 วิ บังคับปิด (รีเซ็ตทุกครั้งที่ Show)
        if (watchdog) { clearTimeout(watchdog); }
        watchdog = setTimeout(function () { active = 0; watchdog = null; doHide(); }, 12000);
    };

    window.HideLoadingOverlay = function () {
        active = Math.max(0, active - 1);
        if (active === 0) {
            if (watchdog) { clearTimeout(watchdog); watchdog = null; }
            doHide();
        }
    };

    // ปุ่ม: disable + หรี่จาง (คงเดิม)
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
