<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function getAuthUserType(): ?int
{
    $jwtSecret = $_ENV['TOKEN'] ?? null;
    if (!$jwtSecret) {
        return null;
    }

    $token = $_COOKIE['token'] ?? '';
    $token = trim((string)$token);
    if ($token === '') {
        return null;
    }

    try {
        $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));

        if (!isset($decoded->user_type)) {
            return null;
        }

        $userType = (int)$decoded->user_type;
        return in_array($userType, [1, 2], true) ? $userType : null;
    } catch (Exception $e) {
        error_log("Error en getAuthUserType(): " . $e->getMessage());
        return null;
    }
}

// Función para validar si una cookie está definida y no vacía
function getSanitizedCookie($name)
{
    return isset($_COOKIE[$name]) ? trim(htmlspecialchars($_COOKIE[$name], ENT_QUOTES, 'UTF-8')) : null;
}


function isUserAdmin(): bool
{
    return getAuthUserType() === 1;
}

function isUserAuthenticated(): bool
{
    return getAuthUserType() !== null;
}


/**
 * Verifica que la solicitud provenga del dominio permitido.
 *
 * @param string $allowedOrigin El dominio permitido.
 * @return void
 */

function corsAllow(array $allowedOrigins): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    // ✅ Sin Origin (Postman / curl / same-origin): no es un caso CORS -> no bloquees
    if ($origin === '') {
        return;
    }

    if (in_array($origin, $allowedOrigins, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        return;
    }

    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Origin not allowed', 'origin' => $origin]);
    exit();
}

// Función para verificar el JWT
function verificarJWT($token)
{
    // Se asume que tienes una función para decodificar el JWT
    // y una clave secreta para verificar la firma (asegúrate de reemplazarla con la tuya)
    $jwtSecret = $_ENV['TOKEN'];

    try {
        // Decodifica el JWT (usa una librería como Firebase JWT o alguna similar)
        $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        // Si el token es inválido
        return null;
    }
}

function verificaTipusUsuari(): void
{
    $token = $_COOKIE['token'] ?? null;

    if (!$token) {
        return;
    }

    $usuario = verificarJWT($token);

    if (!$usuario) {
        return;
    }

    $user_type = $usuario->user_type ?? null;

    switch ($user_type) {
        case 1:
            header('Location: /gestio');
            exit;

        case 2:
            header('Location: /usuaris');
            exit;
    }
}


/**
 * Devuelve info básica del token.
 * - is_admin: true si user_type === 1 (o "1")
 * - user_id:  int|null si viene en el token (user_id/sub/uid)
 */


function getAuthFromToken(): array
{
    $jwtSecret  = $_ENV['TOKEN'] ?? null;
    $cookieName = 'token';

    if (!$jwtSecret || empty($_COOKIE[$cookieName])) {
        return ['is_admin' => false, 'user_id_uuid' => null];
    }

    try {
        $decoded = JWT::decode($_COOKIE[$cookieName], new Key($jwtSecret, 'HS256'));
        $isAdmin = (int)($decoded->user_type ?? 0) === 1;
        $uid     = $decoded->user_id ?? null; // UUID (string) del token
        $uuid    = (is_string($uid) && strlen($uid) <= 64) ? $uid : null;

        return ['is_admin' => $isAdmin, 'user_id_uuid' => $uuid];
    } catch (\Throwable $e) {
        return ['is_admin' => false, 'user_id_uuid' => null];
    }
}

/** Admin = user_type === 1 */
function isAuthenticatedAdmin(): bool
{
    return getAuthFromToken()['is_admin'] === true;
}

/** Nuevo: devuelve el UUID del usuario (string) o null */
function getAuthenticatedUserUuid(): ?string
{
    return getAuthFromToken()['user_id_uuid'];
}

/** Conserva tu función original por compatibilidad (ahora puede devolver null) */
function getAuthenticatedUserId(): ?string
{
    return getAuthFromToken()['user_id_uuid'];
}

function uuidToBin(?string $uuid): ?string
{
    if ($uuid === null || $uuid === '') return null;
    $hex = str_replace('-', '', $uuid);
    return hex2bin($hex);
}

function binToUuid(?string $bin): ?string
{
    if ($bin === null) return null;
    $hex = bin2hex($bin);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($hex, 4));
}

function getData($query, $params = [], $single = false)
{
    global $conn;
    /** @var PDO $conn */

    try {
        // Preparar la consulta
        $stmt = $conn->prepare($query);

        // Si hay parámetros, los vinculamos
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        }

        // Ejecutar la consulta
        $stmt->execute();

        // Si esperamos un solo resultado, usamos fetch()
        if ($single) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            // Si esperamos varios resultados, usamos fetchAll()
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Verificar si hay resultados
        if ($row) {
            return $row;
        } else {
            return ['status' => 'error', 'message' => 'No hi ha cap registre disponible.'];
        }
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Error a la consulta'];
    }
}

function sanitizeNumeros($value, $fieldName = 'ID')
{
    // Validar que sea un número entero positivo (mayor que cero)
    if (!filter_var($value, FILTER_VALIDATE_INT) || (int)$value <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => "$fieldName invàlid, ha de ser un número enter positiu."
        ]);
        exit();
    }
    return (int)$value;
}

function sanitizeSlug($slug, $fieldName = 'slug')
{
    if ($slug) {
        // Eliminar caracteres no permitidos
        $slug = preg_replace('/[^a-zA-Z0-9-_]/', '', $slug);

        // Sanitizar para salida HTML (opcional pero recomendable)
        $slug = htmlspecialchars($slug, ENT_QUOTES, 'UTF-8');

        // Verificar que no quedó vacío
        if (empty($slug)) {
            echo json_encode([
                'status' => 'error',
                'message' => "El valor de $fieldName no es válido."
            ]);
            exit;
        }

        return $slug;
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => "Falta el parámetro $fieldName."
        ]);
        exit;
    }
}

/**
 * Quote seguro para identificadores (tabla, esquema, columna, alias).
 * Soporta "schema.table AS t". No usar para valores.
 */
function qi(string $identifier, PDO $pdo): string
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $parts = preg_split('/\s+AS\s+|\s+as\s+/', $identifier, -1, PREG_SPLIT_NO_EMPTY);
    $main  = $parts[0];
    $alias = $parts[1] ?? null;

    $quote = fn(string $id) => match ($driver) {
        'mysql' => '`' . str_replace('`', '``', $id) . '`',
        'pgsql' => '"' . str_replace('"', '""', $id) . '"',
        default => $id, // fallback
    };

    // Soporta schema.table o table.column si alguna vez lo usas en SELECT
    $dotQuoted = implode('.', array_map($quote, explode('.', $main)));

    return $alias ? $dotQuoted . ' AS ' . $quote($alias) : $dotQuoted;
}
