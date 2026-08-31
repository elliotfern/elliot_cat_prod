<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Config\Database;
use App\Utils\Uuid;

/** @var array $routeParams */

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow([
        'https://elliot.cat',
        'https://dev.elliot.cat',
        'https://elliot.local'
    ]);

    http_response_code(204);
    exit;
}

corsAllow([
    'https://elliot.cat',
    'https://dev.elliot.cat',
    'https://elliot.local'
]);

// ============================================================
// MÉTODO
// ============================================================

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    Response::error(
        MissatgesAPI::error('method_not_allowed'),
        [],
        405
    );
    exit;
}

// ============================================================
// UUID
// ============================================================

$id = $_GET['id'] ?? null;

if (!is_string($id) || !preg_match(
    '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
    $id
)) {
    Response::error(
        MissatgesAPI::error('invalid_data'),
        [
            'id' => 'invalid'
        ],
        400
    );
    exit;
}

// ============================================================
// UUID → BINARY(16)
// ============================================================

try {
    $uuidBytes = Uuid::toBinary($id);
} catch (\Throwable $e) {
    Response::error(
        MissatgesAPI::error('invalid_data'),
        [
            'id' => 'invalid'
        ],
        400
    );
    exit;
}

// ============================================================
// DATABASE
// ============================================================

$db = new Database();
$pdo = $db->getPdo();

// ============================================================
// COMPROBAR QUE EXISTE
// ============================================================

$sqlCheck = "
    SELECT id, pais
    FROM " . Tables::DB_PAISOS . "
    WHERE id = :id
    LIMIT 1
";

try {
    $stmt = $pdo->prepare($sqlCheck);

    $stmt->bindValue(
        ':id',
        $uuidBytes,
        PDO::PARAM_LOB
    );

    $stmt->execute();

    $pais = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pais) {
        Response::error(
            MissatgesAPI::error('not_found'),
            [
                'id' => 'not_found'
            ],
            404
        );
        exit;
    }

    // ========================================================
    // DELETE
    // ========================================================

    $sqlDelete = "
        DELETE FROM " . Tables::DB_PAISOS . "
        WHERE id = :id
    ";

    $stmtDelete = $pdo->prepare($sqlDelete);

    $stmtDelete->bindValue(
        ':id',
        $uuidBytes,
        PDO::PARAM_LOB
    );

    $stmtDelete->execute();

    if ($stmtDelete->rowCount() !== 1) {
        Response::error(
            MissatgesAPI::error('db_error'),
            [
                'id' => 'delete_failed'
            ],
            500
        );
        exit;
    }

    // ========================================================
    // SUCCESS
    // ========================================================

    Response::success(
        MissatgesAPI::success('delete'),
        [
            'id' => $id,
            'pais' => $pais['pais']
        ],
        httpCode: 200
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
