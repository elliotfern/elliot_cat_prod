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

// Check if the request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

function isUuid($s)
{
    return is_string($s) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s);
}


// ACTUALITZAR PAÍS
// URL: 'api/paisos/put'

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

// Validación
$errors = [];

$id = requireField($data, 'id', $errors);
$pais = requireField($data, 'pais', $errors);

if ($id !== null && !isUuid($id)) {
    $errors['id'] = 'invalid';
}

if (!empty($errors)) {
    Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
    exit;
}

// Convertir UUID string a BINARY(16)
$uuid = ramseny::fromString($id);
$uuidBytes = $uuid->getBytes();

$sql = "UPDATE " . Tables::DB_PAISOS . "
        SET pais = :pais,
            updated_at = NOW()
        WHERE id = :id";

try {
    $stmt = $pdo->prepare($sql);

    // ID UUID binario
    $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);
    $stmt->bindValue(':pais', trim($pais), PDO::PARAM_STR);

    if ($stmt->execute()) {

        Response::success(
            MissatgesAPI::success('update'),
            [
                'id'   => $id,
                'pais' => trim($pais)
            ],
            httpCode: 200
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
