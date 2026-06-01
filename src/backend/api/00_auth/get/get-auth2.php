<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

use App\Config\Database;
use App\Config\DatabaseConnection;
use App\Utils\MissatgesAPI;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use App\Utils\Response;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

// Cargar variables de entorno desde .env
$jwtSecret = $_ENV['TOKEN'] ?? null;

//$db = new Database();
//$pdo = DatabaseConnection::getConnection();


header('Content-Type: application/json; charset=utf-8');

// /api/auth/get/?me
if ($slug === 'me') {

    if (!$jwtSecret) {
        Response::error(
            message: 'Server misconfigured (missing TOKEN secret)',
            httpCode: 500
        );
    }

    $token = $_COOKIE['token'] ?? '';

    if (empty($token)) {
        Response::error(
            message: 'Missing token',
            httpCode: 401
        );
    }

    try {

        $decoded = JWT::decode(
            $token,
            new Key($jwtSecret, 'HS256')
        );

        $userType = $decoded->user_type ?? null;

        Response::success(
            message: 'Usuari autenticat correctament',
            data: [
                'authenticated' => true,
                'user_id' => $decoded->user_id ?? null,
                'email' => $decoded->email ?? null,
                'full_name' => $decoded->nom ?? null,
                'user_type' => isset($decoded->user_type)
                    ? (int)$decoded->user_type
                    : null,

                'is_admin' => (
                    isset($decoded->user_type)
                    && (int)$decoded->user_type === 1
                ),
            ],
            httpCode: 200
        );
    } catch (ExpiredException $e) {

        error_log("JWT expirado: " . $e->getMessage());

        Response::error(
            message: 'Token expired',
            httpCode: 401
        );
    } catch (SignatureInvalidException $e) {

        error_log("Firma inválida: " . $e->getMessage());

        Response::error(
            message: 'Invalid signature',
            httpCode: 401
        );
    } catch (BeforeValidException $e) {

        error_log("Token usado antes de tiempo: " . $e->getMessage());

        Response::error(
            message: 'Token not yet valid',
            httpCode: 401
        );
    } catch (Exception $e) {

        error_log("Otro error JWT: " . $e->getMessage());

        Response::error(
            message: $e->getMessage(),
            httpCode: 401
        );
    }
} else if ($slug  === 'login') {

    header('Content-Type: application/json; charset=utf-8');

    $input = json_decode(file_get_contents('php://input'), true);
    error_log('LOGIN ENTERED');
    error_log(json_encode($input));


    $email = $input['email'] ?? null;
    $password = $input['password'] ?? null;

    if (!$email || !$password) {
        Response::error(
            message: 'Email and password are required',
            httpCode: 400
        );
    }

    // VALIDACIÓN EMAIL SIMPLE (sin ValueObject)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Response::error(
            message: 'Invalid email format',
            httpCode: 400
        );
    }


    // 🔥 AQUÍ DEBES CONECTAR TU LÓGICA REAL
    // Ejemplo simple (AJUSTA A TU BD REAL)
    $db = new Database();

    // Preparar la consulta
    $sql = <<<SQL
            SELECT * FROM db_users WHERE email = :email LIMIT 1
            SQL;

    try {

        $params = [':email' => $email];



        $result = $db->getData($sql, $params, false);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
        }

        if (!password_verify($password, $result['password_hash'])) {
            Response::error(
                message: 'Incorrect password',
                httpCode: 401
            );
        }

        if (!$jwtSecret) {
            Response::error(
                message: 'Server misconfigured (missing TOKEN secret)',
                httpCode: 500
            );
        }

        $payload = [
            'user_id' => $result['id'],
            'email' => $result['email'],
            'nom' => $result['full_name'],
            'user_type' => $result['user_type'],
            'iat' => time(),
            'exp' => time() + 604800,
        ];

        $jwt = JWT::encode($payload, $jwtSecret, 'HS256');

        $isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']);

        setcookie(
            'token',
            $jwt,
            [
                'expires' => time() + 10 * 24 * 60 * 60,
                'path' => '/',
                'domain' => $isLocal ? '' : '.elliot.cat',
                'secure' => !$isLocal,
                'httponly' => true,
                'samesite' => $isLocal ? 'Lax' : 'None'
            ]
        );

        Response::success(
            message: 'Login successful',
            data: [
                'user_type' => $result['user_type'],
                'full_name' => $result['full_name']
            ],
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }
} else if ((isset($_GET['logOut']))) {
    // Verifica que el usuario esté autenticado
    session_start();

    $isLocal = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);

    $arr_cookie_options = [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => $isLocal ? '' : '.elliot.cat',
        'secure' => !$isLocal,
        'httponly' => true,
        'samesite' => $isLocal ? 'Lax' : 'None'
    ];

    //Elimina les cookies
    setcookie('token', '', $arr_cookie_options);

    // Además, puedes destruir la sesión si estás utilizando sesiones en PHP
    session_unset();    // Elimina todas las variables de sesión
    session_destroy();  // Destruye la sesión

    // Respuesta en formato JSON o redirige
    Response::success(
        message: 'OK',
        httpCode: 200
    );
}
