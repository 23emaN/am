<?php




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



    $sql_data = "SELECT * FROM tbl_course 

            ORDER BY course_id  DESC;";

    $stmt_data = $pdo_connect->prepare($sql_data);

    $stmt_data->execute();

    $result_data = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

    $stmt_data->closeCursor();



    if ($result_data) {



        $list_data = array();

        foreach ($result_data as $row_data) {



            $temp_array = array(

                "course_id" => $row_data["course_id"] ?? null,

                "course_cover_image" => $row_data["course_cover_image"] ?? null,

                "course_name" => $row_data["course_name"] ?? null,

                "course_price" => $row_data["course_price"] ?? null,

                "course_code_cpd_1" => $row_data["course_code_cpd_1"] ?? null,

                "course_code_cpd_2" => $row_data["course_code_cpd_2"] ?? null,

                "course_code_cpd_3" => $row_data["course_code_cpd_3"] ?? null,

                "course_code_cpd_4" => $row_data["course_code_cpd_4"] ?? null,

                "course_code_cpa_1" => $row_data["course_code_cpa_1"] ?? null,

                "course_code_cpa_2" => $row_data["course_code_cpa_2"] ?? null,

                "course_code_cpa_3" => $row_data["course_code_cpa_3"] ?? null,

                "course_code_cpa_4" => $row_data["course_code_cpa_4"] ?? null,

                "course_display" => $row_data["course_display"] ?? null,

                "course_status" => $row_data["course_status"] ?? null,



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