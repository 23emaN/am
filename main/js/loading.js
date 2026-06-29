/**
 * loading.js
 * -----------------------------------------------------------------------------
 * แถบโหลดด้านบน (top progress bar) แบบมี delay — แทน spinner/skeleton
 * โชว์เฉพาะตอนโหลด "นานเกิน DELAY ms" เท่านั้น (โหลดเร็วจะไม่เห็นอะไร ไม่กระพริบ)
 * override ShowLoadingOverlay/HideLoadingOverlay จาก main.js (โหลดไฟล์นี้หลัง main.js)
 */
(function () {
    "use strict";

    var DELAY = 300;          // ms: ถ้าโหลดเสร็จก่อนเวลานี้ จะไม่โชว์แถบเลย
    var active = 0;           // นับจำนวน request ที่กำลังโหลด (รองรับซ้อนกัน)
    var timer = null;
    var $bar = null;

    function bar() {
        if (!$bar) $bar = $('<div id="cpdth-topbar"></div>').appendTo('body');
        return $bar;
    }

    function start() {
        var $b = bar();
        $b.css({ transition: 'none', width: '0%', opacity: 1 });
        $b[0].offsetWidth; // reflow ก่อนเริ่ม animate
        // ค่อย ๆ คืบไป 90% (ช้าลงเรื่อย ๆ เหมือน progress จริง)
        $b.css({ transition: 'width 3s cubic-bezier(.1,.7,.1,1), opacity .3s ease', width: '90%' });
    }

    function finish() {
        if (!$bar) return;
        $bar.css({ transition: 'width .3s ease, opacity .4s ease', width: '100%' });
        setTimeout(function () {
            $bar.css('opacity', 0);
            setTimeout(function () {
                $bar.css({ transition: 'none', width: '0%' });
            }, 400);
        }, 200);
    }

    window.ShowLoadingOverlay = function () {
        active++;
        if (active === 1 && timer === null) {
            timer = setTimeout(function () { timer = null; start(); }, DELAY);
        }
    };

    window.HideLoadingOverlay = function () {
        active = Math.max(0, active - 1);
        if (active === 0) {
            if (timer !== null) {
                clearTimeout(timer); // โหลดเสร็จก่อน DELAY -> ไม่ต้องโชว์แถบ
                timer = null;
            } else {
                finish();            // โชว์ไปแล้ว -> ปิดแถบให้เต็มแล้วเฟดออก
            }
        }
    };

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
