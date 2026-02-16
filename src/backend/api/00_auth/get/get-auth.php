<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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
        http_response_code(500);
        echo json_encode(['error' => 'Server misconfigured (missing TOKEN secret)']);
        exit;
    }

    // Lee cookie token (si tienes helper, úsalo; si no, esto vale)
    $token = $_COOKIE['token'] ?? '';

    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['authenticated' => false, 'error' => 'Missing token']);
        exit;
    }

    try {
        $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));

        // OJO: esto depende de que el JWT realmente incluya estos campos
        $userType = $decoded->user_type ?? null;
        $fullName = $decoded->full_name ?? null; // si no existe, lo devuelves null
        $userId   = $decoded->user_id ?? null;   // si existe

        if ($userType === null) {
            // token válido, pero sin claim esperado
            http_response_code(200);
            echo json_encode([
                'authenticated' => true,
                'user_type' => null,
                'warning' => 'Token has no user_type claim'
            ]);
            exit;
        }

        echo json_encode([
            'authenticated' => true,
            'user_id' => $decoded->user_id ?? null,
            'email' => $decoded->email ?? null,
            'full_name' => $decoded->nom ?? null,     // <-- aquí
            'user_type' => isset($decoded->user_type) ? (int)$decoded->user_type : null,
            'is_admin' => (isset($decoded->user_type) && (int)$decoded->user_type === 1),
        ]);
        exit;
    } catch (Exception $e) {
        // Token inválido / expirado / manipulado
        error_log("JWT inválido: " . $e->getMessage());
        http_response_code(401);
        echo json_encode(['authenticated' => false, 'error' => 'Invalid token']);
        exit;
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
    echo json_encode(['message' => 'OK']);

    exit;
}
 