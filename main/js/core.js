// แนบ access token จาก localStorage ไปกับทุก request ของ jQuery
// ใช้ ajaxSend เพราะจะทำงานเสมอ แม้ request นั้นจะกำหนด beforeSend ของตัวเองไว้
$(document).ajaxSend(function(event, jqXHR, settings) {
    const token = localStorage.getItem("access_token");
    if (token) {
        jqXHR.setRequestHeader("Authorization", "Bearer " + token);
    }
});

document.addEventListener("DOMContentLoaded", function() {

    $.post("core.php", { "request_state": "list_user", "request_function": "user_profile", }, function (response) {
        if(response.result == 1){
            $(".ShowUserFullname").html(response.data.full_name);
            $(".ShowUserRole").html(response.data.role_name);
            $(".ShowUserAvatar").attr("src", response.data.avatar || "../template/assets/images/administrator.jpg");

            FilterSidebarByAccess(response.data.access_menus);
            GuardPageByAccess(response.data.access_menus);

            setInterval(KeepSessionAlive, 30000);
        }else{
            Swal.fire({
                title: "แจ้งเตือน",
                html: '<span class="fw-bold text-danger">'+response.msg+'</span>',
                icon: "error",
                showConfirmButton: false,
                allowOutsideClick: false,
                timer: 2000,
                timerProgressBar: true,
                didClose: () => {
                    window.location.replace("logout.php");
                }
            });
        }
    },"json");
});

// ซ่อนเมนู sidebar ที่ผู้ใช้ไม่มีสิทธิ์ (จับคู่ด้วยข้อความ .title = menu_name)
// fail-open: ถ้าไม่มีข้อมูลสิทธิ์ (ว่าง) จะไม่กรอง (โชว์ครบ กันล็อกผู้ใช้)
function FilterSidebarByAccess(allowed) {
    if (!allowed || !allowed.length) { return; }
    $(".sidebar-area .menu-item").each(function () {
        var title = $(this).find(".title").first().text().trim();
        if (title && allowed.indexOf(title) === -1) {
            $(this).hide();
        }
    });
    // ซ่อนหัวข้อหมวดที่ไม่เหลือเมนูที่มองเห็น
    $(".sidebar-area .menu-title").each(function () {
        if ($(this).nextUntil(".menu-title", ".menu-item:visible").length === 0) {
            $(this).hide();
        }
    });
}

// บล็อกระดับหน้า: ถ้าผู้ใช้ไม่มีสิทธิ์เมนูของหน้านี้ -> เด้งกลับหน้าแรก
// (หน้าแรก/หน้าที่ไม่อยู่ในแมป = เข้าได้เสมอ กัน loop; fail-open ถ้าไม่มีข้อมูลสิทธิ์)
var PAGE_MENU_MAP = {
    course: "คอร์สเรียน", course_fromadd: "คอร์สเรียน", course_category: "คอร์สเรียน",
    lesson_manage: "คอร์สเรียน", lesson_preview: "คอร์สเรียน", course_type: "คอร์สเรียน",
    order: "คำสั่งซื้อคอร์สเรียน", order_detail: "คำสั่งซื้อคอร์สเรียน",
    etax: "ใบกำกับภาษี (E-Tax)", etax_view: "ใบกำกับภาษี (E-Tax)", etax_edit: "ใบกำกับภาษี (E-Tax)", etax_invoice: "ใบกำกับภาษี (E-Tax)",
    user: "ผู้ใช้/ลูกค้า", user_edit: "ผู้ใช้/ลูกค้า",
    verify_history: "ประวัติการยืนยันตัวตน",
    verify_request: "ยืนยันตัวตนผู้ใช้งาน",
    coupon: "คูปองส่วนลด", coupon_fromadd: "คูปองส่วนลด", coupon_edit: "คูปองส่วนลด",
    banner: "แบนเนอร์", banner_fromadd: "แบนเนอร์", banner_edit: "แบนเนอร์",
    admin: "ผู้ดูแลระบบ", admin_fromadd: "ผู้ดูแลระบบ", admin_edit: "ผู้ดูแลระบบ"
};

function GuardPageByAccess(allowed) {
    if (!allowed || !allowed.length) { return; } // fail-open
    var page = (location.pathname.split("/").pop() || "").replace(".php", "");
    var required = PAGE_MENU_MAP[page];
    if (!required) { return; } // หน้าแรก/ไม่อยู่ในแมป -> เข้าได้
    if (allowed.indexOf(required) === -1) {
        Swal.fire({
            title: "ไม่มีสิทธิ์เข้าถึง",
            html: '<span class="text-secondary">คุณไม่มีสิทธิ์เข้าถึงหน้านี้</span>',
            icon: "warning", confirmButtonText: "กลับหน้าแรก", allowOutsideClick: false
        }).then(function () { window.location.replace("home"); });
    }
}

async function KeepSessionAlive() {
    try {
        const response = await fetch("core/keepSession.php", {
            method: "POST",
            headers: {
                "Cache-Control": "no-cache",
                "Authorization": "Bearer " + (localStorage.getItem("access_token") || "")
            }
        });
        if (response.ok) {
            const data = await response.json();
            if (data.result === 0) {
                Swal.fire({
                    title: "แจ้งเตือน",
                    html: '<span class="fw-bold text-danger">Access Token Expired</span>',
                    icon: "error",
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    timer: 2000,
                    timerProgressBar: true,
                    didClose: () => {
                        window.location.replace("logout.php");
                    },
                });
            }
        } else {
            Swal.fire({
                title: "แจ้งเตือน",
                html: '<span class="fw-bold text-danger">' + response.status + "</span>",
                icon: "error",
                showConfirmButton: false,
                allowOutsideClick: false,
                timer: 2000,
                timerProgressBar: true,
                didClose: () => {
                    window.location.replace("logout.php");
                },
            });
        }
    } catch (error) {
        Swal.fire({
            title: "แจ้งเตือน",
            html: '<span class="fw-bold text-danger">' + error + "</span>",
            icon: "error",
            showConfirmButton: false,
            allowOutsideClick: false,
            timer: 2000,
            timerProgressBar: true,
            didClose: () => {
                window.location.replace("logout.php");
            },
        });
    }
}