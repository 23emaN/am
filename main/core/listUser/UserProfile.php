<?php

    use App\Utility\Auth;
    use App\Utility\Response;
    use App\Database\Connection;

    $access_token = Auth::requireUserToken();
    $user_id = $access_token->user_id ?? null;

    $full_name = $access_token->fullname ?? '';
    $role_name = $access_token->access_level ?? '';
    $avatar = $access_token->profile_image ?? '';

    // เมนูที่ผู้ใช้คนนี้มีสิทธิ์เข้าถึง (สำหรับกรอง sidebar ฝั่ง client)
    $access_menus = [];
    try {
        $pdo = (new Connection())->getPdo();
        if ($pdo && $user_id) {
            $stmt = $pdo->prepare(
                "SELECT s.menu_name
                 FROM tbl_user_access ua
                 JOIN tbl_slidebar s ON s.menu_id = ua.menu_id
                 WHERE ua.user_id = :uid AND s.active_status = '1'"
            );
            $stmt->execute([':uid' => $user_id]);
            $access_menus = array_map(fn($r) => (string) $r['menu_name'], $stmt->fetchAll(PDO::FETCH_ASSOC));
            $stmt->closeCursor();
        }
    } catch (\Throwable $e) {
        $access_menus = []; // error -> ไม่กรองเมนู (กัน sidebar พัง)
    }

    Response::json(1, 'Success', [
        'full_name'    => $full_name,
        'role_name'    => $role_name,
        'avatar'       => $avatar,
        'access_menus' => $access_menus,
    ]);
?>
