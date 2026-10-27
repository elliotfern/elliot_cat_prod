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
// DELETE persona
// ruta: ?persona&id={uuid}
// -------------------------
if ($slug === 'persona') {

    $id = $_GET['id'] ?? null;

    if (!$id || !Uuid::isValid($id)) {
        Response::error(
            MissatgesAPI::error('invalid_data'),
            ['id' => 'invalid_uuid'],
            400
        );
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Verificar existencia
        $sqlCheck = "
            SELECT 1
            FROM " . Tables::PERSONES . "
            WHERE id = :id
            LIMIT 1
        ";

        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->bindValue(':id', Uuid::toBinary($id), PDO::PARAM_LOB);
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

        // Eliminar relaciones con grupos
        $sqlRel = "
            DELETE FROM " . Tables::PERSONES_GRUPS_RELACIONS . "
            WHERE persona_id = :id
        ";

        $stmtRel = $pdo->prepare($sqlRel);
        $stmtRel->bindValue(':id', Uuid::toBinary($id), PDO::PARAM_LOB);

        if (!$stmtRel->execute()) {
            $pdo->rollBack();

            Response::error(
                MissatgesAPI::error('db_error'),
                [
                    'sqlState' => $stmtRel->errorCode(),
                    'info' => $stmtRel->errorInfo(),
                ],
                500
            );
            exit;
        }

        // Eliminar persona
        $sqlDelete = "
            DELETE FROM " . Tables::PERSONES . "
            WHERE id = :id
            LIMIT 1
        ";

        $stmtDelete = $pdo->prepare($sqlDelete);
        $stmtDelete->bindValue(':id', Uuid::toBinary($id), PDO::PARAM_LOB);

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
            [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
            500
        );

        exit;
    }
}
