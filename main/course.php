<?php include "header.php"; ?>



<div class="container-fluid">

    <div class="main-content d-flex flex-column">



        <?php include "navbar.php"; ?>



        <div class="main-content-container overflow-hidden">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 px-4">

                <div>
                        <ol class="breadcrumb" id="PageBreadcrumb">
                            <li class="breadcrumb-item active">
                                <strong>คอร์สเรียน</strong>
                            </li>
                        </ol>
                </div>
            </div>

        </div>

        <div id="GetTable">
        </div>
        <div id="GetFormAdd" style="display: none;">
        </div>



    <?php include "footer.php"; ?>



    </div>

</div>





<?php include "script.php"; ?>

</body>

</html>


<script>

    $(document).ready(function () {

        // เช็คว่าอยู่หน้าไหนตอนนี้
        if (window.location.hash === '#add') {
            LoadAddCourseForm();
        } else {
            LoadData();
        }

    });



    function LoadData() {

        // จดจำหน้าที่อยู่
        if (window.location.hash === '#add') {
            history.replaceState("", document.title, window.location.pathname);
        }

        $.ajax({

            beforeSend: function () {

                ShowLoadingOverlay("#GetTable");

            },

            type: "POST",

            url: "core.php",

            data: {

                request_state: "list_course",

                request_function: "get_list_course",

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

                HideLoadingOverlay("#GetTable");

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

                ShowLoadingOverlay("#GetTable");

            },

            type: "POST",

            url: "view/listCourse/GetTable.php",

            data: JSON.stringify(payload),

            contentType: "application/json; charset=utf-8",

            processData: false,

            dataType: "html",

            success: function (response) {

                $("#GetTable").html(response);

                $("#GetTable").show();
                $("#GetFormAdd").hide();

                $("#PageBreadcrumb").html(`
                    <li class="breadcrumb-item active">
                        <strong>คอร์สเรียน</strong>
                    </li>
                `);

                $("#PageTable").DataTable({

                    responsive: true,

                    autoWidth: false,

                    pageLength: 10,

                    language: {

                        url: '../template/assets/js/data-table-th.json'

                    },

                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "ทั้งหมด"]]

                });



            },

            complete: function () {

                HideLoadingOverlay("#GetTable");

            },

            error: function (jqXHR, exception) {

                ShowErrorAjax(jqXHR, exception);

            }

        });

    }

    function LoadAddCourseForm() {
        window.location.hash = 'add';
        $.ajax({
            beforeSend: function () {
                ShowLoadingOverlay("#GetTable");
            },
            type: "POST",
            url: "core.php",
            data: {
                request_state: "list_course",
                request_function: "get_add_course_form",
            },
            dataType: "html",
            success: function (response) {
                $("#GetFormAdd").html(response);
                $("#GetTable").hide();
                $("#GetFormAdd").show();

                $("#PageBreadcrumb").html(`
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0);" onclick="LoadData();" class="text-decoration-none text-primary">คอร์สเรียน</a>
                    </li>
                    <li class="breadcrumb-item active">
                        <strong>เพิ่มคอร์สเรียน</strong>
                    </li>
                `);
            },
            complete: function () {
                HideLoadingOverlay("#GetTable");
            },
            error: function (jqXHR, exception) {
                ShowErrorAjax(jqXHR, exception);
            }
        });
    }

</script>