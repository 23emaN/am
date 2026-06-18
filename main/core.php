<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Bangkok');

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Routing\Router;

$request_state    = isset($_POST['request_state']) ? trim($_POST['request_state']) : '';
$request_function = isset($_POST['request_function']) ? trim($_POST['request_function']) : '';

$router = new Router();

$routes = [
    'list_user'                => [
        'user_profile' => __DIR__ . '/core/listUser/UserProfile.php',
    ],

    'list_course'               => [
        'get_list_course'       => __DIR__ . '/core/listCourse/GetListCourse.php',
        'get_select_category'   => __DIR__ . '/core/listCourse/GetSelectCategory.php',

    ],
    'listCourseCategory'               => [
        'get_list_category'       => __DIR__ . '/core/listCourseCategory/GetListCategory.php',
        'add_category'       => __DIR__ . '/core/listCourseCategory/AddCategory.php',
    ],
];
foreach ($routes as $state => $actions) {
    foreach ($actions as $action => $file) {
        $router->post($state, $action, $file);
    }
}

$router->dispatch($request_state, $request_function);
