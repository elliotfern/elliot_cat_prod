<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Función para validar si una cookie está definida y no vacía
function getSanitizedCookie($name)
{
    return isset($_COOKIE[$name]) ? trim(htmlspecialchars($_COOKIE[$name], ENT_QUOTES, 'UTF-8')) : null;
}


function isUserAdmin(): bool
{
    // Cargar variables de entorno desde .env
    $jwtSecret = $_ENV['TOKEN'];

    if (!isset($_COOKIE['token'])) {
        return false;
    }

    $token = trim($_COOKIE['token']);

    try {
        $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));

        // Comprobamos si el usuario es admin (user_type = 1)
        if (isset($decoded->user_type) && $decoded->user_type == 1) {
            return true;
        }
    } catch (Exception $e) {
        // Token inválido, expirado o manipulado
        error_log("Error en isUserAdmin(): " . $e->getMessage());
    }

    return false;
}

function isUserUsuari(): bool
{
    // Cargar variables de entorno desde .env
    $jwtSecret = $_ENV['TOKEN'];

    if (!isset($_COOKIE['token'])) {
        return false;
    }

    $token = trim($_COOKIE['token']);

    try {
        $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));

        // Comprobamos si el usuario es admin (user_type = 1)
        if (isset($decoded->user_type) && ($decoded->user_type == 1 || $decoded->user_type == 2)) {
            return true;
        }
    } catch (Exception $e) {
        // Token inválido, expirado o manipulado
        error_log("Error en isUserAdmin(): " . $e->getMessage());
    }

    return false;
}

/**
 * Verifica que la solicitud provenga del dominio permitido.
 *
 * @param string $allowedOrigin El dominio permitido.
 * @return void
 */
function checkReferer($allowedOrigin)
{
    // Verificar que la cabecera 'Referer' esté presente y sea válida
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $allowedOrigin) === 0) {
        // El Referer contiene la URL del dominio permitido
        header("Access-Control-Allow-Origin: " . $allowedOrigin);
    } else {
        // Si la cabecera 'Referer' no es válida, denegar el acceso
        header("HTTP/1.1 403 Forbidden");
        echo json_encode(['error' => 'Accés no permés']);
        exit();
    }
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

function verificaTipusUsuari()
{
    // Obtener el token de las cookies
    $token = $_COOKIE['token'] ?? null;

    if ($token) {
        $usuario = verificarJWT($token);

        if ($usuario) {
            $user_type = $usuario->user_type; // Tipo de usuario en el JWT

            // Dependiendo del tipo de usuario, redirige a una página diferente
            if ($user_type == 1) { // Admin
                header('Location: /gestio');
            } elseif ($user_type == 2) { // User
                header('Location: /usuaris');
            }
            // Si no es admin ni user, deja que continúe en la página actual
        }
    } else {
        return;
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
function getAuthenticatedUserId(): ?int
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
