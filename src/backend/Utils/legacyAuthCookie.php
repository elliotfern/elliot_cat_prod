<?php
// legacy_auth_bootstrap.php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function legacy_bootstrap_user_from_jwt(): void
{
    $jwtSecret = $_ENV['TOKEN'] ?? null;
    if (!$jwtSecret) return;

    $token = $_COOKIE['token'] ?? '';
    if ($token === '') return;

    try {
        $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));

        // Mantener compatibilidad legacy:
        // legacy esperaba: $_COOKIE['user_id'] === '1' para admin
        if (isset($decoded->user_type)) {
            $_COOKIE['user_id'] = (string) ((int)$decoded->user_type); // <-- clave
            $_COOKIE['user_type'] = (string) ((int)$decoded->user_type);
        }

        // Extra (por si te sirve en templates)
        if (isset($decoded->nom)) {
            $_COOKIE['nom'] = (string) $decoded->nom;
        }
        if (isset($decoded->email)) {
            $_COOKIE['email'] = (string) $decoded->email;
        }
        if (isset($decoded->user_id)) {
            $_COOKIE['uuid'] = (string) $decoded->user_id;
        }
    } catch (Exception $e) {
        // Token inválido: no tocamos cookies legacy
        // error_log("JWT inválido (legacy bootstrap): " . $e->getMessage());
        return;
    }
}
