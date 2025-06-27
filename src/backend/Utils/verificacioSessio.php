<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function data_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
}

function verificarSesion()
{
    // Inicia la sesión si no está ya iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Cargar variables de entorno desde .env
    $jwtSecret = $_ENV['TOKEN'];

    // Verifica si la cookie del token existe y es válida
    if (!isset($_COOKIE['token'])) {
        header('Location: /entrada'); // Redirige a login si no existe el token
        exit();
    }

    $token = trim($_COOKIE['token']);

    try {
        // Decodificar el token JWT
        $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));

        // Obtener user_type del payload
        $userType = $decoded->user_type ?? null;

        // Verificar si user_type es 1 (admin) o 2 (usuario regular)
        if (!in_array($userType, [1, 2])) {
            header('Location: /entrada'); // Redirige si el user_type no es válido (no es admin ni usuario regular)
            exit();
        }
    } catch (Exception $e) {
        // Si el token es inválido, ha expirado o no es manipulable
        error_log("Error al verificar sesión: " . $e->getMessage());
        header('Location: /entrada'); // Redirige a login si el token no es válido
        exit();
    }
}
