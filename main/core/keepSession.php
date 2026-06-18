<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $projectRoot = dirname(__DIR__, 2);
    require_once $projectRoot . '/vendor/autoload.php';

    use Dotenv\Dotenv;
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    use Throwable;
    use App\Utility\Response;

    header('Content-Type: application/json; charset=utf-8');

    $result = 0;
    $accessToken = $_SESSION['access_token'] ?? '';

    if (is_string($accessToken) && $accessToken !== '') {
        Dotenv::createImmutable($projectRoot)->safeLoad();
        $secretKey = $_ENV['JWT_SECRET'] ?? '';

        if ($secretKey !== '') {
            try {
                $decodedToken = JWT::decode($accessToken, new Key($secretKey, 'HS256'));
                if (($decodedToken->exp ?? 0) >= time()) {
                    $result = 1;
                } else {
                    unset($_SESSION['access_token']);
                }
            } catch (Throwable $exception) {
                unset($_SESSION['access_token']);
            }
        }
    }

    Response::json($result, $result === 1 ? 'OK' : 'Session expired', ['result' => $result]);
?>