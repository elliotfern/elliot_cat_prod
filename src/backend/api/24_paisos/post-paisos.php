<?php

use Ramsey\Uuid\Uuid as ramseny;
use App\Utils\Uuid;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Config\Database;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

function isUuid($s)
{
    return is_string($s) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s);
}


// INSERIR NOU PAIS
// URL: 'api/paisos/post'

$input_data = file_get_contents("php://input");
$data = json_decode($input_data, true);

if (!is_array($data)) {
    Response::error(MissatgesAPI::error('bad_request'), ['data' => 'invalid'], 400);
    exit;
}

// Helpers
function requireField(array $data, string $key, array &$errors)
{
    if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === null) {
        $errors[$key] = 'required';
        return null;
    }
    return $data[$key];
}

function optionalField(array $data, string $key)
{
    return (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null)
        ? $data[$key]
        : null;
}

// Validación
$errors = [];

$pais_ca = requireField($data, 'pais_ca', $errors);
$pais_en = optionalField($data, 'pais_en');

if (!empty($errors)) {
    Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
    exit;
}

// Generar UUIDv7
$uuid = ramseny::uuid7();
$uuidBytes = $uuid->getBytes();   // para BINARY(16)
$uuidString = $uuid->toString();  // para devolver al frontend

$sql = "INSERT INTO " . Tables::DB_PAISOS . " (
              id, pais_ca, pais_en, created_at, updated_at
          ) VALUES (
              :id,
              :pais_ca, 
              :pais_en,
              NOW(),
              NOW()
          )";

try {
    $stmt = $pdo->prepare($sql);

    // ID UUIDv7 binario
    $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);
    $stmt->bindValue(':pais_ca', $pais_ca, PDO::PARAM_STR);
    $stmt->bindValue(':pais_en', $pais_en, $pais_en === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

    if ($stmt->execute()) {

        Response::success(
            MissatgesAPI::success('create'),
            [
                'id'      => $uuidString,
                'pais_ca' => $pais_ca,
                'pais_en' => $pais_en,
            ],
            httpCode: 201
        );
        exit;
    }

    Response::error(
        MissatgesAPI::error('db_error'),
        [
            'sqlState' => $stmt->errorCode(),
            'info' => $stmt->errorInfo(),
        ],
        500
    );
    exit;
} catch (\Throwable $e) {
    Response::error(
        MissatgesAPI::error('internal_error'),
        [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ],
        500
    );
    exit;
}
