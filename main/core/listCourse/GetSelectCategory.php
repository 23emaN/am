
<?php

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}



use App\Utility\Auth;

use App\Utility\Response;

use App\Database\Connection;



$access_token = Auth::requireUserToken();

$user_id = $access_token->user_id ?? null;



$db_instance = new Connection();

$pdo_connect = $db_instance->getPdo();



if (!$pdo_connect) {

    Response::json(0, 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', null);

}



if ($user_id) {



    $sql_data = "SELECT * FROM tbl_course_group 

            ORDER BY group_id   DESC;";

    $stmt_data = $pdo_connect->prepare($sql_data);

    $stmt_data->execute();

    $result_data = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

    $stmt_data->closeCursor();



    if ($result_data) {



        $list_data = array();

        foreach ($result_data as $row_data) {



            $temp_array = array(

                "group_id" => $row_data["group_id"] ?? null,

                "group_name" => $row_data["group_name"] ?? null,

            );

            array_push($list_data, $temp_array);

        }



        Response::json(1, 'Success', [

            'list_data' => $list_data,

        ]);

    } else {

        Response::json(1, 'No data found', [

            'list_data' => [],

        ]);

    }

} else {

    Response::json(0, 'Unauthorized', null);

}

?>