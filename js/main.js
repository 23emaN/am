function ShowErrorAjax(jqXHR, exception) {
    let msg = '';
    if (jqXHR.status === 0) {
        msg = 'Not connect.\n Verify Network.';
    } else if (jqXHR.status == 404) {
        msg = 'Requested page not found. [404]';
    } else if (jqXHR.status == 500) {
        msg = 'Internal Server Error [500].';
    } else if (exception === 'parsererror') {
        msg = 'Requested JSON parse failed.';
    } else if (exception === 'timeout') {
        msg = 'Time out error.';
    } else if (exception === 'abort') {
        msg = 'Ajax request aborted.';
    } else {
        msg = 'Uncaught Error.\n' + jqXHR.responseText;
    }
    Swal.fire({
        title: "แจ้งเตือน",
        html: "พบปัญหาการบันทึก กรุณาติดต่อผู้ดูแลระบบ<br>"+ EscapeHTML(msg),
        icon: "error",
        showConfirmButton: true,
    });
}
function EscapeHTML(str) {
  return str.replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
function ShowLoadingOverlay(selector) {
    $(selector).LoadingOverlay("show", {
        zIndex: 9999,
        image : "",
        fontawesome: "ri-refresh-line text-primary",
        fontawesomeAnimation: "spin",
        size: "50",
        maxSize: "200",
        minSize: "50",
    });
}
function HideLoadingOverlay(selector) {
    $(selector).LoadingOverlay("hide", true);
}

function ShowLoadingButton(selector) {
    let $el = $(selector);
    if ($el.is('button') || $el.attr('type') === 'submit') {
        $el.prop('disabled', true);
    }
    $el.LoadingOverlay("show", {
        zIndex: 9999,
        image : "",
        fontawesome: "ri-refresh-line text-primary",
        fontawesomeAnimation: "spin",
        size: "60", 
        maxSize: "80",
        minSize: "10",
    });
}
function HideLoadingButton(selector) {
    let $el = $(selector);
    if ($el.is('button') || $el.attr('type') === 'submit') {
        $el.prop('disabled', false);
    }
    $el.LoadingOverlay("hide", true);
}

function NumberFormat(number, decimals = 0, decPoint = '.', thousandsSep = ',') {
    if (number === null || number === undefined || number === '') {
        number = 0;
    }
    const n = Number(String(number).replace(/,/g, ''));
    if (!Number.isFinite(n)) {
        number = 0;
    }
    const dec = Math.max(0, parseInt(decimals, 10) || 0);
    const fixed = Number(number).toFixed(dec);
    let [intPart, fracPart] = fixed.split('.');
    intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);
    return dec > 0
        ? intPart + decPoint + fracPart
        : intPart;
}

function ParseDate(input) {
    let parts = input.split('/');
    return new Date(parts[2], parts[1] - 1, parts[0]);
}


/**
 * ThaiDate
 * -----------------------------
 * @param {string} datetime  'YYYY-MM-DD HH:ii:ss'
 * @param {string} format    รูปแบบวันที่ (ดูรายการด้านล่าง)
 * @returns {string}         วันที่ภาษาไทย หรือ "รูปแบบวันที่ไม่ถูกต้อง"
 *
 * FORMAT OPTIONS:
 *  - full_date_and_time      => 19 ธันวาคม 2568 เวลา 10:10
 *  - full_date_short_time    => 19 ธันวาคม 2568 - 10:10
 *  - short_date_short_time   => 19 ธ.ค. 2568 - 10:10
 *  - full_date               => 19 ธันวาคม 2568
 *  - short_date              => 19 ธ.ค. 2568
 *  - no_date_full_month      => ธันวาคม 2568
 *  - no_date_short_month     => ธ.ค. 2568
 *  - number_date_thai        => 19/12/2568
 *  - number_date_eng         => 19/12/2025
 */
function ThaiDate(datetime, format) {
    const ERROR = 'รูปแบบวันที่ไม่ถูกต้อง';

    if (typeof datetime !== 'string' || typeof format !== 'string') {
        return ERROR;
    }

    const monthTHFull = [
        null, 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];

    const monthTHShort = [
        null, 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
    ];

    // ✅ รองรับทั้ง YYYY-MM-DD และ YYYY-MM-DD HH:ii:ss
    const match = datetime.trim().match(
        /^(\d{4})-(\d{2})-(\d{2})(?: (\d{2}):(\d{2}):(\d{2}))?$/
    );

    if (!match) return ERROR;

    const year   = Number(match[1]);
    const month  = Number(match[2]);
    const day    = Number(match[3]);
    const hour   = match[4] !== undefined ? Number(match[4]) : 0;
    const minute = match[5] !== undefined ? Number(match[5]) : 0;
    const second = match[6] !== undefined ? Number(match[6]) : 0;

    const date = new Date(year, month - 1, day, hour, minute, second);

    // strict validation (กัน 2025-02-30)
    if (
        date.getFullYear() !== year ||
        date.getMonth() !== month - 1 ||
        date.getDate() !== day ||
        date.getHours() !== hour ||
        date.getMinutes() !== minute ||
        date.getSeconds() !== second
    ) {
        return ERROR;
    }

    const BE = year + 543;
    const pad2 = (n) => String(n).padStart(2, '0');
    const timeHM = `${pad2(hour)}:${pad2(minute)}`;

    switch (format) {

        case 'full_date_and_time':
            return `${day} ${monthTHFull[month]} ${BE} เวลา ${timeHM}`;

        case 'full_date_short_time':
            return `${day} ${monthTHFull[month]} ${BE} - ${timeHM}`;

        case 'short_date_short_time':
            return `${day} ${monthTHShort[month]} ${BE} - ${timeHM}`;

        case 'full_date':
            return `${day} ${monthTHFull[month]} ${BE}`;

        case 'short_date':
            return `${day} ${monthTHShort[month]} ${BE}`;

        case 'no_date_full_month':
            return `${monthTHFull[month]} ${BE}`;

        case 'no_date_short_month':
            return `${monthTHShort[month]} ${BE}`;

        case 'number_date_thai':
            return `${pad2(day)}/${pad2(month)}/${BE}`;

        case 'number_date_eng':
            return `${pad2(day)}/${pad2(month)}/${year}`;

        default:
            return ERROR;
    }
}
