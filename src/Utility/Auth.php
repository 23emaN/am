<?php
namespace App\Utility;

use App\Database\Connection;
use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;
use Throwable;

class Auth
{

    public static function requireUserToken(): object
    {

        if (! isset($_SESSION['access_token'])) {

            Response::json(0, 'Unauthorized', null);

        }

        static $envLoaded = false;

        if (! $envLoaded) {

            Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();

            $envLoaded = true;

        }

        $secretKey = $_ENV['JWT_SECRET'] ?? '';

        if ($secretKey === '') {

            Response::json(0, 'Secret key not found', null);

        }

        try {

            $token = JWT::decode($_SESSION['access_token'], new Key($secretKey, 'HS256'));

        } catch (Throwable $exception) {

            Response::json(0, 'Invalid token', null);

        }

        if (($token->exp ?? 0) < time()) {

            Response::json(0, 'Token expired', null);

        }

        $access_token = self::ensureActiveUser($token);

        return $access_token;

    }

    private static function ensureActiveUser(object $token): object
    {

        if (empty($token->jti)) {

            Response::json(0, 'Invalid token', null);

        }

        $db = (new Connection())->getPdo();

        $sql = "SELECT u.user_id, CONCAT(u.user_firstname, ' ', u.user_lastname) AS fullname, NULL AS profile_image, NULL AS n_name, 'Administrator' AS access_level

                FROM tbl_login_token lt

                JOIN tbl_user u ON lt.user_id = u.user_id

                WHERE lt.token_code = :token_code AND u.user_status = 1 AND lt.end_datetime IS NULL AND lt.expire_datetime > NOW()

                LIMIT 1";

        $stmt = $db->prepare($sql);

        $stmt->execute([

            ':token_code' => $token->jti ?? '',

        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $row) {

            unset($_SESSION['access_token']);

            Response::json(0, 'User revoked', null);

        }

        $payload = [

            'user_id'       => $row['user_id'],

            'fullname'      => $row['fullname'],

            'profile_image' => $row['profile_image'],

            'n_name'        => $row['n_name'],

            'access_level'  => $row['access_level'],

        ];

        return (object) $payload;

    }

}
