<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function data_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
}

/**
 * Verifica sesión por JWT (cookie "token").
 * - Redirige a /entrada si no hay token o es inválido.
 * - Inyecta compatibilidad legacy: $_COOKIE['user_id'] = user_type (string)
 * - Devuelve el objeto decoded por si quieres usarlo.
 */
function verificarSesion(): object
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $jwtSecret = $_ENV['TOKEN'] ?? null;
    if (!$jwtSecret) {
        // Si no hay secret, es un fallo de config: mejor echar fuera
        error_log("Missing TOKEN secret in env");
        header('Location: /entrada');
        exit();
    }

    if (empty($_COOKIE['token'])) {
        header('Location: /entrada');
        exit();
    }

    $token = trim($_COOKIE['token']);

    try {
        $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));

        $userType = isset($decoded->user_type) ? (int)$decoded->user_type : null;

        // Solo permitimos user_type 1 (admin) o 2 (usuario)
        if (!in_array($userType, [1, 2], true)) {
            header('Location: /entrada');
            exit();
        }

        // ✅ Compatibilidad legacy (tu if antiguo)
        $_COOKIE['user_id'] = (string)$userType;      // legacy esperaba '1' para admin
        $_COOKIE['user_type'] = (string)$userType;

        // ✅ Extra útil para plantillas/legacy
        if (isset($decoded->nom))   $_COOKIE['nom'] = (string)$decoded->nom;
        if (isset($decoded->email)) $_COOKIE['email'] = (string)$decoded->email;
        if (isset($decoded->user_id)) $_COOKIE['uuid'] = (string)$decoded->user_id;

        // (opcional) también en $_SESSION para no depender de $_COOKIE en templates
        $_SESSION['user_type'] = $userType;
        $_SESSION['nom'] = $decoded->nom ?? null;
        $_SESSION['email'] = $decoded->email ?? null;
        $_SESSION['uuid'] = $decoded->user_id ?? null;

        return $decoded;
    } catch (Exception $e) {
        error_log("Error al verificar sesión: " . $e->getMessage());
        header('Location: /entrada');
        exit();
    }
}



function verificarAdmin(): object
{
    $decoded = verificarSesion(); // ya valida token + user_type 1 o 2

    if ((int)($decoded->user_type ?? 0) !== 1) {
        header('Location: /entrada');
        exit();
    }

    return $decoded;
}
