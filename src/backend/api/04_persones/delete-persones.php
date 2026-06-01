<?php

use App\Utils\Uuid;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;

global $conn;
/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    Response::error(MissatgesAPI::error('method_not_allowed'), [], 405);
    exit;
}

function isUuid($s)
{
    return is_string($s)
        && preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $s
        );
}

// -------------------------
// DELETE persona
// ruta: ?persona&id={uuid}
// -------------------------
if ($slug === 'persona') {

    $id = $_GET['id'] ?? null;

    if (!$id || !isUuid($id)) {
        Response::error(
            MissatgesAPI::error('invalid_data'),
            ['id' => 'invalid_uuid'],
            400
        );
        exit;
    }

    try {

        $conn->beginTransaction();

        // Verificar existencia
        $sqlCheck = "
            SELECT 1
            FROM " . Tables::PERSONES . "
            WHERE id = :id
            LIMIT 1
        ";

        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindValue(':id', uuid::toBinary($id), PDO::PARAM_LOB);
        $stmtCheck->execute();

        if (!$stmtCheck->fetchColumn()) {

            $conn->rollBack();

            Response::error(
                MissatgesAPI::error('not_found'),
                ['id' => 'not_exists'],
                404
            );
            exit;
        }

        // Eliminar relaciones grupos
        $sqlRel = "
            DELETE FROM " . Tables::PERSONES_GRUPS_RELACIONS . "
            WHERE persona_id = :id
        ";

        $stmtRel = $conn->prepare($sqlRel);
        $stmtRel->bindValue(':id', uuid::toBinary($id), PDO::PARAM_LOB);

        if (!$stmtRel->execute()) {

            $conn->rollBack();

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

        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bindValue(':id', uuid::toBinary($id), PDO::PARAM_LOB);

        if (!$stmtDelete->execute()) {

            $conn->rollBack();

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

        $conn->commit();

        Response::success(
            MissatgesAPI::success('delete'),
            [
                'id' => $id
            ],
            200
        );

        exit;
    } catch (\Throwable $e) {

        if ($conn->inTransaction()) {
            $conn->rollBack();
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
