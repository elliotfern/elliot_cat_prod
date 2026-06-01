<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Cargar variables de entorno desde .env
$jwtSecret = $_ENV['TOKEN'];

$token = getSanitizedCookie('token');

if (!empty($token)) {
    try {
        // Verifica y decodifica el token
        $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));

        // Treure el nom d'usuari
        if (isset($decoded->nom)) {
            echo json_encode(['nom' => $decoded->nom]);
            exit;
        }
    } catch (Exception $e) {
        // Token inválido, expirado o manipulado
        error_log("JWT inválido: " . $e->getMessage());
    }
}

// Si no cumple, no es admin
echo json_encode(['success' => 'error']);
