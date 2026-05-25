<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use App\Utils\Response;

// Cargar variables de entorno desde .env
$jwtSecret = $_ENV['TOKEN'] ?? null;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// /api/auth/get/?me
if (isset($_GET['me'])) {

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
} else if ((isset($_GET['logOut']))) {
    // Verifica que el usuario esté autenticado
    session_start();

    $arr_cookie_options = array(
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => 'elliot.cat',
        'secure' => true,         // igual que al crearlas
        'httponly' => true,       // igual que al crearlas
        'samesite' => 'Strict'    // igual que al crearlas
    );

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
