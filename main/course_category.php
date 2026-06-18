<?php include "header.php"; ?>



<div class="container-fluid">

    <div class="main-content d-flex flex-column">



        <?php include "navbar.php"; ?>



        <div class="main-content-container overflow-hidden">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 px-2">

                <div>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="course.php">คอร์สเรียน</a>
                            </li>
                            <li class="breadcrumb-item active">
                                <strong>หมวดหมู่ของคอร์สเรียน</strong>
                            </li>
                        </ol>
                </div>
            </div>

        </div>

        <div id="GetTable"></div>




        <?php include "footer.php"; ?>

    </div>
</div>

    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">

        <div class="modal-dialog modal-md modal-dialog-centered">

            <div class="modal-content animated fadeIn" id="LoadingMyModal">

                <div id="showModal"></div>

            </div>

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

                request_state: "listCourseCategory",

                request_function: "get_list_category",

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

        };



        $.ajax({

            beforeSend: function () {

                ShowLoadingOverlay("#GetTable");

            },

            type: "POST",

            url: "view/listCourseCategory/GetTable.php",

            data: JSON.stringify(payload),

            contentType: "application/json; charset=utf-8",

            processData: false,

            dataType: "html",

            success: function (response) {

                $("#GetTable").html(response);

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

    function GetModalAdd() {

        $.ajax({

            beforeSend: function () {

                ShowLoadingOverlay("#myModal");

            },
            type: "POST",

            url: "view/listCourseCategory/GetModalAdd.php",

            dataType: "html",

            success: function (response) {

                $("#showModal").html(response);

                $("#myModal").modal("show");

            },
            complete: function () {

                HideLoadingOverlay("#myModal");

            },

            error: function (jqXHR, exception) {
                ShowErrorAjax(jqXHR, exception);
            }
        });
    }
</script>