<?php

use App\Config\Database;
use App\Utils\Tables;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Uuid;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);


// Verificar que el método de la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}


// GET : llistat de contactes
// URL: https://elliot.cat/api/contactes/get/llistatContactes
if ($slug === "llistatContactes") {

    $sql = <<<SQL
                SELECT c.id, c.nom, c.cognoms, c.email, c.tel_1, c.tel_2, c.tel_3, c.data_naixement, c.web, t.tipus, p.pais_ca, c.adreca
                FROM %s AS c
                LEFT JOIN %s AS t ON c.tipus_id = t.id
                LEFT JOIN %s AS p ON c.pais_id = p.id
                ORDER BY c.cognoms ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_CONTACTES, $pdo),
        qi(Tables::DB_CONTACTES_TIPUS, $pdo),
        qi(Tables::DB_PAISOS, $pdo)
    );

    try {
        $result = $db->getData($query, [], false);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // RUTA GET - contacteId
} else if ($slug === 'contacteId') {
    $id = $_GET['id'];

    $sql = <<<SQL
                SELECT c.id, c.nom, c.cognoms, c.email, c.tel_1, c.tel_2, c.tel_3, c.data_naixement, c.web, c.pais_id, c.tipus_id, c.adreca,
                t.tipus, p.pais_ca
                FROM %s AS c
                LEFT JOIN %s AS t ON c.tipus_id = t.id
                LEFT JOIN %s AS p ON c.pais_id = p.id
                WHERE c.id = :id
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_CONTACTES, $pdo),
        qi(Tables::DB_CONTACTES_TIPUS, $pdo),
        qi(Tables::DB_PAISOS, $pdo)
    );

    try {
        $params = [':id' => Uuid::toBinary($id)];
        $result = $db->getData($query, $params, true);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
