<?php
   
    use App\Utility\Auth;
    use App\Utility\Response;

    $access_token = Auth::requireUserToken();

    $full_name = $access_token->fullname ?? '';
    $role_name = $access_token->access_level ?? '';
    $avatar = $access_token->profile_image ?? '';

    Response::json(1, 'Success', [
        'full_name' => $full_name,
        'role_name' => $role_name,
        'avatar' => $avatar,
    ]);
?>