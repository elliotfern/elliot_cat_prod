<?php

use App\Config\Database;
use App\Utils\Uuid;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;

$db = new Database();
$pdo = $db->getPdo();

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    Response::error(MissatgesAPI::error('method_not_allowed'), [], 405);
    exit;
}

// -------------------------
// DELETE organitzacio
// ruta: https://elliot.cat/api/historia/delete/organitzacio&id={uuid}
// -------------------------
if ($slug === 'organitzacio') {

    $id = $_GET['id'] ?? null;

    if (!is_string($id) || !Uuid::isValid($id)) {
        Response::error(
            MissatgesAPI::error('invalid_data'),
            ['id' => 'invalid_uuid'],
            400
        );
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Verificar existencia de la organización
        $sqlCheck = "
            SELECT 1
            FROM " . Tables::DB_HISTORIA_ORGANITZACIONS . "
            WHERE id = :id
            LIMIT 1
        ";

        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->bindValue(
            ':id',
            Uuid::toBinary($id),
            PDO::PARAM_LOB
        );
        $stmtCheck->execute();

        if (!$stmtCheck->fetchColumn()) {
            $pdo->rollBack();

            Response::error(
                MissatgesAPI::error('not_found'),
                ['id' => 'not_exists'],
                404
            );
            exit;
        }

        // Eliminar organización
        $sqlDelete = "
            DELETE FROM " . Tables::DB_HISTORIA_ORGANITZACIONS . "
            WHERE id = :id
            LIMIT 1
        ";

        $stmtDelete = $pdo->prepare($sqlDelete);
        $stmtDelete->bindValue(
            ':id',
            Uuid::toBinary($id),
            PDO::PARAM_LOB
        );

        if (!$stmtDelete->execute()) {
            $pdo->rollBack();

            Response::error(
                MissatgesAPI::error('db_error'),
                [
                    'sqlState' => $stmtDelete->errorCode(),
                    'info' => $stmtDelete->errorInfo(),
                ],
                500
            );
            exit;
        }

        $pdo->commit();

        Response::success(
            MissatgesAPI::success('delete'),
            ['id' => $id],
            httpCode: 200
        );

        exit;
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        Response::error(
            MissatgesAPI::error('internal_error'),
            [],
            500
        );

        exit;
    }
}
