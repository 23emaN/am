<?php
date_default_timezone_set('Asia/Bangkok');

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Routing\Router;

$request_state    = isset($_POST['request_state']) ? trim($_POST['request_state']) : '';
$request_function = isset($_POST['request_function']) ? trim($_POST['request_function']) : '';

$router = new Router();

$routes = [
    'list_user'                => [
        'user_profile'   => __DIR__ . '/core/listUser/UserProfile.php',
        'get_list_user'  => __DIR__ . '/core/listUser/GetListUser.php',
        'get_user'       => __DIR__ . '/core/listUser/GetUser.php',
        'update_user'    => __DIR__ . '/core/listUser/UpdateUser.php',
        'delete_user'    => __DIR__ . '/core/listUser/DeleteUser.php',
    ],

    'list_coupon'              => [
        'get_list_coupon' => __DIR__ . '/core/listCoupon/GetListCoupon.php',
        'add_coupon'      => __DIR__ . '/core/listCoupon/AddCoupon.php',
        'get_coupon'      => __DIR__ . '/core/listCoupon/GetCoupon.php',
        'update_coupon'   => __DIR__ . '/core/listCoupon/UpdateCoupon.php',
        'delete_coupon'   => __DIR__ . '/core/listCoupon/DeleteCoupon.php',
    ],

    'list_course'               => [
        'get_list_course'       => __DIR__ . '/core/listCourse/GetListCourse.php',
        'get_select_category'   => __DIR__ . '/core/listCourse/GetSelectCategory.php',
        'add_course'            => __DIR__ . '/core/listCourse/AddCourse.php',
        'get_course'            => __DIR__ . '/core/listCourse/GetCourse.php',
        'update_course'         => __DIR__ . '/core/listCourse/UpdateCourse.php',
        'delete_course'         => __DIR__ . '/core/listCourse/DeleteCourse.php',
    ],
    'listCourseCategory'               => [
        'get_list_category'       => __DIR__ . '/core/listCourseCategory/GetListCategory.php',
        'add_category'       => __DIR__ . '/core/listCourseCategory/AddCategory.php',
    ],
    'listCourseType'               => [
        'get_list_type'           => __DIR__ . '/core/listCourseType/GetListType.php',
        'add_type'                => __DIR__ . '/core/listCourseType/AddType.php',
    ],
    'lesson'                    => [
        'get_list_lesson'       => __DIR__ . '/core/lesson/GetListLesson.php',
        'add_lesson'            => __DIR__ . '/core/lesson/AddLesson.php',
        'get_lesson'            => __DIR__ . '/core/lesson/GetLesson.php',
        'update_lesson'         => __DIR__ . '/core/lesson/UpdateLesson.php',
        'update_video'          => __DIR__ . '/core/lesson/UpdateVideo.php',
        'upload_video'          => __DIR__ . '/core/lesson/UploadVideo.php',
        'delete_lesson'         => __DIR__ . '/core/lesson/DeleteLesson.php',
    ],
    'question'                  => [
        'get_list_question'     => __DIR__ . '/core/question/GetListQuestion.php',
        'add_question'          => __DIR__ . '/core/question/AddQuestion.php',
        'get_question'          => __DIR__ . '/core/question/GetQuestion.php',
        'update_question'       => __DIR__ . '/core/question/UpdateQuestion.php',
        'delete_question'       => __DIR__ . '/core/question/DeleteQuestion.php',
        'upload_question'       => __DIR__ . '/core/question/UploadQuestion.php',
    ],
    'lesson_file'               => [
        'get_list_lesson_file'  => __DIR__ . '/core/lessonFile/GetListLessonFile.php',
        'add_lesson_file'       => __DIR__ . '/core/lessonFile/AddLessonFile.php',
        'delete_lesson_file'    => __DIR__ . '/core/lessonFile/DeleteLessonFile.php',
    ],
    'exam'                      => [
        'get_list_exam'         => __DIR__ . '/core/exam/GetListExam.php',
        'add_exam'              => __DIR__ . '/core/exam/AddExam.php',
        'get_exam'              => __DIR__ . '/core/exam/GetExam.php',
        'update_exam'           => __DIR__ . '/core/exam/UpdateExam.php',
        'delete_exam'           => __DIR__ . '/core/exam/DeleteExam.php',
        'upload_exam'           => __DIR__ . '/core/exam/UploadExam.php',
    ],
];
foreach ($routes as $state => $actions) {
    foreach ($actions as $action => $file) {
        $router->post($state, $action, $file);
    }
}

$router->dispatch($request_state, $request_function);
