<?php


use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Utils\Uuid;

/** @var array $routeParams */
$endpoint = $routeParams[0] ?? null;

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


// Galeria imatges ID
// ruta GET => "/api/public/imatges/get/galeriaImatgesId?slug=eeeeee"
if ($endpoint === 'galeriaImatgesId') {
    $slug = $_GET['slug'] ?? null;

    if (!$slug) {
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
                    g.publica,
                    g.slug
                FROM %s g
                WHERE slug = :slug
                AND publica = 1
                SQL;

        $queryGaleria = sprintf(
            $sqlGaleria,
            qi(Tables::DB_IMATGES_GALERIES, $pdo)
        );

        $params = [
            ':slug' => $slug
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
                ':galeria_id' => $galeria['id']
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
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
