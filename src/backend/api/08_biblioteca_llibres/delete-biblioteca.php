<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

// Solo DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    Response::error(MissatgesAPI::error('method_not_allowed'), [], 405);
    exit;
}

// DELETE relació llibre-autor (por rel_id autoincrement)
if (isset($_GET['llibreAutorRel'])) {

    $relIdRaw = (string)$_GET['llibreAutorRel'];
    $relId = (int)$relIdRaw;

    if ($relId <= 0) {
        Response::error(MissatgesAPI::error('invalid_data'), ['llibreAutorRel' => 'invalid'], 400);
        exit;
    }

    try {
        global $conn;

        // (Opcional) comprobar existencia y devolver info útil
        $qFind = "SELECT id FROM " . Tables::LLIBRES_AUTORS . " WHERE id = :id LIMIT 1";
        $stFind = $conn->prepare($qFind);
        $stFind->bindValue(':id', $relId, PDO::PARAM_INT);
        $stFind->execute();

        $exists = $stFind->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            Response::error(MissatgesAPI::error('not_found'), ['llibreAutorRel' => $relId], 404);
            exit;
        }

        $sql = "DELETE FROM " . Tables::LLIBRES_AUTORS . " WHERE id = :id LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $relId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            Response::success(MissatgesAPI::success('delete'), ['rel_id' => $relId], 200);
            exit;
        }

        Response::error(
            MissatgesAPI::error('db_error'),
            ['sqlState' => $stmt->errorCode(), 'info' => $stmt->errorInfo()],
            500
        );
        exit;
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('internal_error'),
            ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()],
            500
        );
        exit;
    }
}

// Si no coincide ninguna ruta
Response::error(MissatgesAPI::error('bad_request'), ['route' => 'unknown'], 400);
exit;
