<?php


use App\Config\Database;
use App\Infrastructure\Security\Auth\AuthFactory;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Utils\Uuid;
use App\Utils\Validator;

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

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Imatge ID
// ruta GET => "/api/auxiliars/imatges/get/imatgeId?id=eeeeee"
if ($slug === 'imatgeId') {

    $id = $_GET['id'] ?? null;

    $sql = <<<SQL
            SELECT i.id, i.nameImg, i.extension, i.typeImg, i.nom, i.alt, i.dateCreated, i.dateModified
            FROM %s i
            WHERE i.id = :id
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_IMATGES, $pdo)
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

    // Galeria imatges ID
    // ruta GET => "/api/auxiliars/imatges/get/galeriaImatgesId?id=eeeeee"
} else if ($slug === 'galeriaImatgesId') {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        Response::error(
            MissatgesAPI::error('not_found'),
            [],
            404
        );
        return;
    }

    try {

        // ========================================================
        // GALERÍA
        // ========================================================

        $sqlGaleria = <<<SQL
                SELECT
                    g.id,
                    g.nom,
                    g.directori,
                    g.alt,
                    g.dateCreated,
                    g.dateModified
                FROM %s g
                WHERE g.id = :id
                SQL;

        $queryGaleria = sprintf(
            $sqlGaleria,
            qi(Tables::DB_IMATGES_GALERIES, $pdo)
        );

        $params = [
            ':id' => Uuid::toBinary($id)
        ];

        $galeria = $db->getData(
            $queryGaleria,
            $params,
            true
        );

        if (empty($galeria)) {

            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );

            return;
        }


        // ========================================================
        // IMÁGENES DE LA GALERÍA
        // ========================================================

        $sqlImatges = <<<SQL
                SELECT
                    i.id,
                    i.nameImg,
                    i.extension,
                    i.typeImg,
                    i.nom,
                    i.alt,
                    gi.ordre
                FROM %s gi
                INNER JOIN %s i
                    ON i.id = gi.imatge_id
                WHERE gi.galeria_id = :galeria_id
                ORDER BY gi.ordre ASC
                SQL;

        $queryImatges = sprintf(
            $sqlImatges,
            qi(Tables::DB_IMATGES_GALERIES_IMG, $pdo),
            qi(Tables::DB_IMATGES, $pdo)
        );

        $imatges = $db->getData(
            $queryImatges,
            [
                ':galeria_id' => Uuid::toBinary($id)
            ],
            false
        );


        // ========================================================
        // CONSTRUIR RESULTADO
        // ========================================================

        $result = $galeria;

        $result['imatges'] = $imatges ?: [];


        // ========================================================
        // RESPUESTA
        // ========================================================

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

    // Galeria imatges
    // ruta GET => "/api/auxiliars/imatges/get/galeriaImatges"
} else if ($slug === 'galeriaImatges') {

    // ============================================================
    // SQL
    // ============================================================

    $sql = <<<SQL
        SELECT
            g.id,
            g.nom,
            g.directori,
            g.alt,
            g.dateCreated,
            g.dateModified
        FROM %s g
        ORDER BY g.nom ASC
        SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_IMATGES_GALERIES, $pdo)
    );

    // ============================================================
    // OBTENER DATOS
    // ============================================================

    try {

        $result = $db->getData(
            $query,
            [],
            false
        );

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
