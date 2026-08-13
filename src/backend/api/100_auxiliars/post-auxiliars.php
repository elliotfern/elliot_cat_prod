<?php

use App\Utils\Uuid;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Config\Database;
use Ramsey\Uuid\Uuid as RamseyUuid;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

$db = new Database();
$pdo = $db->getPdo();

header('Content-Type: application/json; charset=utf-8');

// CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);


function isUuid($s)
{
    return is_string($s) && preg_match(
        '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
        $s
    );
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


// ==============================
// POST IDIOMA
// ==============================
if ($slug === 'idioma') {

    // Leer JSON
    $input_data = file_get_contents("php://input");
    $data = json_decode($input_data, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid'], 400);
        exit;
    }

    // Validación
    $errors = [];

    $idioma_ca        = requireField($data, 'idioma_ca', $errors);

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        exit;
    }

    // UUID v7
    $uuid = RamseyUuid::uuid7();
    $uuidBytes = $uuid->getBytes();
    $uuidString = Uuid::toString($uuidBytes);

    $sql = "INSERT INTO " . Tables::DB_IDIOMES . " (
                id,
                idioma_ca
            ) VALUES (
                :id,
                :idioma_ca
            )";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);
        $stmt->bindValue(':idioma_ca', $idioma_ca, PDO::PARAM_STR);

        if ($stmt->execute()) {
            Response::success(
                MissatgesAPI::success('create'),
                [
                    'id' => $uuidString,
                    'idioma_ca' => $idioma_ca
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
            httpCode: 500
        );
        exit;
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage(),
            ],
            httpCode: 500
        );
        exit;
    }
    // ==============================
    // POST IDIOMA
    // ==============================
} else if ($slug === 'editorial') {

    // Leer JSON
    $input_data = file_get_contents("php://input");
    $data = json_decode($input_data, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid'], 400);
        exit;
    }

    // Validación
    $errors = [];

    $editorial        = requireField($data, 'editorial', $errors);
    $web        = requireField($data, 'web', $errors);
    $pais_id        = $data['pais_id'];

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        exit;
    }

    // UUID v7
    $uuid = RamseyUuid::uuid7();
    $uuidBytes = $uuid->getBytes();
    $uuidString = Uuid::toString($uuidBytes);

    $pais_id_bin = Uuid::toBinary($pais_id);

    $sql = "INSERT INTO " . Tables::LLIBRES_EDITORIALS . " (
                id,
                editorial,
                web,
                pais_id
            ) VALUES (
                :id,
                :editorial,
                :web,
                :pais_id
            )";

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $uuidBytes, PDO::PARAM_LOB);
        $stmt->bindValue(':editorial', $editorial, PDO::PARAM_STR);
        $stmt->bindValue(':web', $web, PDO::PARAM_STR);
        $stmt->bindValue(':pais_id', $pais_id_bin, PDO::PARAM_LOB);

        if ($stmt->execute()) {
            Response::success(
                MissatgesAPI::success('create'),
                [
                    'id' => $uuidString,
                    'editorial' => $editorial
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
            httpCode: 500
        );
        exit;
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('internal_error'),
            [
                'message' => $e->getMessage(),
            ],
            httpCode: 500
        );
        exit;
    }
}
