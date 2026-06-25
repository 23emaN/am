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