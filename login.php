<!DOCTYPE html>

<html lang="th">



    <head>

        <meta charset="utf-8">

        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">



        <title>CPDTH - Login</title>



        <link rel="icon" type="image/png" href="template/assets/images/favicon.png">

        <link rel="stylesheet" href="template/assets/css/font.css">

        <link rel="stylesheet" href="template/assets/css/sidebar-menu.css">

        <link rel="stylesheet" href="template/assets/css/simplebar.css">    

        <link rel="stylesheet" href="template/assets/css/apexcharts.css">

        <link rel="stylesheet" href="template/assets/css/prism.css">

        <link rel="stylesheet" href="template/assets/css/rangeslider.css">

        <link rel="stylesheet" href="template/assets/css/quill.snow.css">

        <link rel="stylesheet" href="template/assets/css/google-icon.css">

        <link rel="stylesheet" href="template/assets/css/remixicon.css">

        <link rel="stylesheet" href="template/assets/css/swiper-bundle.min.css">

        <link rel="stylesheet" href="template/assets/css/fullcalendar.main.css">

        <link rel="stylesheet" href="template/assets/css/jsvectormap.min.css">

        <link rel="stylesheet" href="template/assets/css/lightpick.css">

        <link rel="stylesheet" href="template/assets/css/style.css">

        <link rel="stylesheet" href="template/assets/css/toastr.min.css">



        <link rel="stylesheet" href="template/assets/css/custom.css">

        <link rel="stylesheet" href="template/assets/css/web.css">

    </head>



    <body class="boxed-size bg-white">

        <!-- Start Preloader Area -->

        <div class="preloader" id="preloader">

            <div class="preloader">

                <div class="waviy position-relative">

                    <span class="d-inline-block">C</span>

                    <span class="d-inline-block">P</span>

                    <span class="d-inline-block">D</span>

                    <span class="d-inline-block">T</span>

                    <span class="d-inline-block">H</span>

                </div>

            </div>

        </div>

        <!-- End Preloader Area -->



        <!-- Start Main Content Area -->

        <div class="container">

            <div class="main-content d-flex flex-column p-0">

                <div class="m-auto m-1230">

                    <div class="row align-items-center">

                        <div class="col-lg-6 d-none d-lg-block">

                            <img src="template/assets/images/login.jpg" class="rounded-3" alt="login">

                        </div>

                        <div class="col-lg-6">

                            <div class="mw-480 ms-lg-auto">

                                <h3 class="fs-28 mb-4">CPDTH LOGIN</h3>

                                <form>

                                    <div class="form-group mb-4">

                                        <label class="label text-secondary">Username</label>

                                        <input type="text" id="username" name="username" class="form-control h-55" placeholder="Username">

                                    </div>

                                    <div class="form-group mb-4">

                                        <label class="label text-secondary">Password</label>

                                        <input type="password" id="password" name="password" class="form-control h-55" placeholder="Password">

                                    </div>

                                    <div class="form-group mb-4">

                                        <button type="button" class="btn btn-sm btn-primary fw-medium py-2 px-3 w-100" onclick="Login()">

                                            <div class="d-flex align-items-center justify-content-center py-1">

                                                <i class="material-symbols-outlined text-white fs-20 me-2">login</i>

                                                <span>เข้าสู่ระบบ</span>

                                            </div>

                                        </button>

                                    </div>

                                    <div class="row">

                                        <!-- <div class="col-12">

                                            <a href="#" target="_blank" class="btn btn-outline-secondary bg-transparent w-100 py-2 hover-bg mb-4" style="border-color: #D6DAE1;">

                                                <img src="template/image/LINE_logo.svg" alt="LINE" class="me-2" style="width: 25px; height: 25px;"> เข้าสู่ระบบด้วย LINE

                                            </a>

                                        </div> -->

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- Link Of JS File -->

        <script src="template/assets/js/jquery-3.1.1.min.js"></script>

        <script src="template/assets/js/bootstrap.bundle.min.js"></script>

        <script src="template/assets/js/sidebar-menu.js"></script>

        <script src="template/assets/js/dragdrop.js"></script>

        <script src="template/assets/js/rangeslider.min.js"></script>

        <script src="template/assets/js/quill.min.js"></script>

        <script src="template/assets/js/data-table.js"></script>

        <script src="template/assets/js/prism.js"></script>

        <script src="template/assets/js/clipboard.min.js"></script>

        <script src="template/assets/js/feather.min.js"></script>

        <script src="template/assets/js/simplebar.min.js"></script>

        <script src="template/assets/js/apexcharts.min.js"></script>

        <script src="template/assets/js/echarts.js"></script>

        <script src="template/assets/js/swiper-bundle.min.js"></script>

        <script src="template/assets/js/fullcalendar.main.js"></script>

        <script src="template/assets/js/jsvectormap.min.js"></script>

        <script src="template/assets/js/world-merc.js"></script>

        <script src="template/assets/js/moment.min.js"></script>

        <script src="template/assets/js/lightpick.js"></script>

        <script src="template/assets/js/custom/custom.js"></script>

        <script src="template/assets/js/toastr.min.js"></script>

        <script src="template/assets/js/sweetalert2@11.js"></script>

        <script src="template/assets/js/loadingoverlay.js"></script>

        <script src="js/main.js"></script>

        <script>

            document.getElementById('password').addEventListener('keypress', function(e) {

                if (e.key === 'Enter') {

                    Login();

                }

            });



            document.getElementById('username').addEventListener('keypress', function(e) {

                if (e.key === 'Enter') {

                    Login();

                }

            });

            

            function Login() {

                const username = $("#username").val();

                const password = $("#password").val();



                if(username == "" || password == ""){

                    Swal.fire({

                        title: "แจ้งเตือน",

                        html: '<span class="fw-bold text-danger">กรุณากรอก Username และ Password</span>',

                        icon: "warning",

                        showConfirmButton: false,

                        allowOutsideClick: false,

                        timer: 2000,

                        timerProgressBar: true,

                    });

                    return false;

                }



                $.ajax({

                    beforeSend: function() {

                        ShowLoadingOverlay(".container");

                    },

                    type: "POST",

                    url: "core.php",

                    data: {

                        request_state: "login",

                        request_function: "login",

                        username: username,

                        password: password,

                    },

                    dataType: "json",

                    success: function (response) {

                        if(response.result == 1){

                            localStorage.setItem("access_token", response.data.access_token);

                            Swal.fire({

                                title: "แจ้งเตือน",

                                html: '<span class="fw-bold text-success">'+response.msg+'</span>',

                                icon: "success",

                                showConfirmButton: false,

                                allowOutsideClick: false,

                                timer: 2000,

                                timerProgressBar: true,

                                didClose: () => {

                                    sessionStorage.setItem("cpdth_show_preloader", "1"); window.location.replace("main/index");

                                }

                            });

                        }else if(response.result == 0){

                            Swal.fire({

                                title: "แจ้งเตือน",

                                html: '<span class="fw-bold text-danger">'+response.msg+'</span>',

                                icon: "warning",

                                showConfirmButton: false,

                                allowOutsideClick: false,

                                timer: 2000,

                                timerProgressBar: true,

                            });

                        }else{

                            Swal.fire({

                                title: "แจ้งเตือน",

                                html: "<span class='fw-bold text-danger'>"+EscapeHTML(response.msg)+"</span>",

                                icon: "error",

                                showConfirmButton: true,

                            });

                            return false;

                        }

                    },

                    complete: function() {

                        HideLoadingOverlay(".container");

                    },

                    error: function(jqXHR, exception) {

                        ShowErrorAjax(jqXHR, exception);

                    }

                });

            }   

        </script>

    </body>

</html>