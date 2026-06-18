<?php include "header.php"; ?>



<div class="container-fluid">

    <div class="main-content d-flex flex-column">



        <?php include "navbar.php"; ?>



        <div class="main-content-container overflow-hidden">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 px-4">

                <div>
                    <!-- <h2 class="font-bold">คอร์สเรียน</h2> -->
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="course.php">คอร์สเรียน</a>
                            </li>
                            <li class="breadcrumb-item active">
                                <strong>เพิ่มคอร์สเรียน</strong>
                            </li>
                        </ol>
                </div>

                <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">

                    <ol class="breadcrumb align-items-center mb-0 lh-1">

                        <li class="breadcrumb-item">

                            <a href="home" class="d-flex align-items-center text-decoration-none">

                                <!-- <span class="material-symbols-outlined text-primary me-1">school</span> -->

                                <span class="text-secondary fw-medium hover">เพิ่มคอร์สเรียนใหม่</span>

                            </a>

                        </li>

                    </ol>

                </nav>

            </div>

        </div>

        <div id="GetFormAdd">

        </div>
    </div>



    <?php include "footer.php"; ?>

</div>

</div>



<?php include "script.php"; ?>

<script>
     $(document).ready(function () {


            LoadData();

    });

        function LoadData() {

        $.ajax({

            beforeSend: function () {

                ShowLoadingOverlay("#GetFormAdd");

            },

            type: "POST",

            url: "core.php",

            data: {

                request_state: "list_course",

                request_function: "get_select_category",

            },

            dataType: "json",

            success: function (response) {

                if (response.result == 1) {

                    RenderListCourse(response.data);

                } else {

                    Swal.fire({

                        title: "แจ้งเตือน",

                        html: '<span class="fw-bold text-danger">' + response.msg + '</span>',

                        icon: "error",

                        showConfirmButton: false,

                        allowOutsideClick: false,

                        timer: 2000,

                        timerProgressBar: true,

                    });

                }

            },

            complete: function () {

                HideLoadingOverlay("#GetFormAdd");

            },

            error: function (jqXHR, exception) {

                ShowErrorAjax(jqXHR, exception);

            }

        });

    }

    function RenderListCourse(data) {

        const payload = {

            list_data: data.list_data,

            // _schema: "list_event_v1"

        };



        $.ajax({

            beforeSend: function () {

                ShowLoadingOverlay("#GetFormAdd");

            },

            type: "POST",

            url: "view/listCourse/FormAddCourse.php",

            data: JSON.stringify(payload),

            contentType: "application/json; charset=utf-8",

            processData: false,

            dataType: "html",

            success: function (response) {

                $("#GetFormAdd").html(response);
                 $('#course_category').select2({
            width: '100%'
        });

            },

            complete: function () {

                HideLoadingOverlay("#GetFormAdd");

            },

            error: function (jqXHR, exception) {

                ShowErrorAjax(jqXHR, exception);

            }

        });

    }
</script>

</body>

</html>

