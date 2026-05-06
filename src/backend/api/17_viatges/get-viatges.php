<?php

use App\Config\Database;
use App\Utils\Tables;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Uuid;
use App\Utils\AdminMiddleware;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Metode no permès']);
    exit();
}

// 1. Visites realizades en un espai
// ruta GET => "/api/viatges/get/llistatVisitesEspai"
if ($slug === 'llistatVisitesEspai') {

    AdminMiddleware::handle();

    $espai = $_GET['espai'];

    $sql = <<<SQL
                SELECT v.id, vl.slug, vl.viatge, v.dataVisita
                FROM %s AS v
                LEFT JOIN %s AS vl ON v.viatge_id = vl.id
                LEFT JOIN %s AS p ON v.espai_id = p.id
                WHERE p.slug = :slug
                ORDER BY v.dataVisita ASC;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_VIATGES_ESPAIS_VISITATS, $pdo),
        qi(Tables::DB_VIATGES, $pdo),
        qi(Tables::DB_VIATGES_ESPAIS, $pdo),
    );

    try {

        $params = [':slug' => $espai];
        $result = $db->getData($query, $params);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // 2. Llistat espais
    // ruta GET => "/api/viatges/get/llistatEspais"
} else if ($slug === 'llistatEspais') {

    AdminMiddleware::handle();

    $sql = <<<SQL
                SELECT e.id, e.nom, e.slug, e.any_fundacio, e.descripcio, e.tipus_id, e.web, e.ciutat_id, e.img_id, e.coordinades_latitud, e.coordinades_longitud, e.dateCreated, e.dateModified, t.tipus, c.ciutat
                FROM %s AS e
                LEFT JOIN %s AS t ON e.tipus_id = t.id
                LEFT JOIN %s AS c ON e.ciutat_id = c.id
                ORDER BY e.nom ASC;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_VIATGES_ESPAIS, $pdo),
        qi(Tables::DB_VIATGES_ESPAIS_TIPUS, $pdo),
        qi(Tables::DB_CIUTATS, $pdo)
    );

    try {

        $result = $db->getData($query);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // 2. Llistat espais visitats
    // ruta GET => "/api/viatges/get/llistatEspaisVisitats"
} else if ($slug === 'llistatEspaisVisitats') {

    AdminMiddleware::handle();

    $sql = <<<SQL
                SELECT 
                ev.id, ev.espai_id, ev.viatge_id, ev.dataVisita, e.id as idEspai, e.nom, e.slug, v.viatge, v.slug AS viatgeSlug
                FROM %s AS ev
                LEFT JOIN %s AS e ON ev.espai_id = e.id
                LEFT JOIN %s AS v ON ev.viatge_id = v.id
                ORDER BY ev.dataVisita DESC;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_VIATGES_ESPAIS_VISITATS, $pdo),
        qi(Tables::DB_VIATGES_ESPAIS, $pdo),
        qi(Tables::DB_VIATGES, $pdo)
    );

    try {

        $result = $db->getData($query);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // 3. Fitxa espai
    // ruta GET => "/api/viatges/get/fitxaEspai?espai=palau-reial"
} else if ($slug === 'fitxaEspai') {
    $espai = $_GET['espai'];

    AdminMiddleware::handle();

    $sql = <<<SQL
                SELECT p.id, p.nom, p.slug, p.any_fundacio, p.descripcio, p.tipus_id, p.web, p.ciutat_id, p.coordinades_longitud, p.coordinades_latitud, p.dateCreated, p.dateModified, p.img_id,
                a.tipus, i.nameImg AS img, i.alt, c.ciutat
                FROM %s AS p
                LEFT JOIN %s AS c ON p.ciutat_id = c.id
                LEFT JOIN %s AS a ON p.tipus_id = a.id
                LEFT JOIN %s AS i ON p.img_id = i.id
                WHERE p.slug = :espai
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_VIATGES_ESPAIS, $pdo),
        qi(Tables::DB_CIUTATS, $pdo),
        qi(Tables::DB_VIATGES_ESPAIS_TIPUS, $pdo),
        qi(Tables::DB_IMATGES, $pdo)
    );

    try {

        $params = [':espai' => $espai];
        $result = $db->getData($query, $params);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // 6. Llistat de viatges
    // ruta GET => "/api/viatges/get/?llistatViatges"
} else if ($slug === 'llistatViatges') {

    $sql = <<<SQL
            SELECT l.id, l.viatge, l.descripcio, l.dataInici, l.dataFi, l.slug, l.pais_id, c.pais_ca
            FROM %s AS l
            LEFT JOIN %s AS c ON l.pais_id = c.id
            ORDER BY l.dataInici DESC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_VIATGES, $pdo),
        qi(Tables::DB_PAISOS, $pdo),
    );

    try {

        $result = $db->getData($query);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // 6. Llistat espais visitats durant un viatge determinat
    // ruta GET => "/api/viatges/get/?llistatEspaisViatge=perpinya"
} else if (isset($_GET['llistatEspaisViatge'])) {
    $slug = $_GET['llistatEspaisViatge'];

    $query = "SELECT p.nom, p.id, v.dataVisita, c.ciutat, p.slug
            FROM db_travel_places_visited AS v
            INNER JOIN db_viatges_llistat AS l ON v.idViatge = l.id
            INNER JOIN db_travel_places AS p ON p.id = v.espId
            INNER JOIN db_cities AS c ON c.id = idCiutat
            WHERE l.slug = :slug
            GROUP BY p.id
            ORDER BY v.dataVisita";


    // 7. Detalls fitxa espai
    // ruta GET => "/api/viatges/get/fitxaEspaiDetalls?espai=perpinya"
} else if ($slug === 'fitxaEspaiDetalls') {

    $espai = $_GET['espai'];

    $sql = <<<SQL
            SELECT p.id, p.nom, p.any_fundacio, p.descripcio, p.web, p.ciutat_id, c.ciutat, a.tipus, i.nameImg AS img, i.alt, i.nameImg, p.coordinades_longitud, p.coordinades_latitud, p.dateCreated, p.dateModified
            FROM %s AS p
            LEFT JOIN %s AS c ON p.ciutat_id = c.id
            LEFT JOIN %s AS a ON p.tipus_id = a.id
            LEFT JOIN %s AS i ON p.img_id = i.id
            WHERE p.slug = :slug
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_VIATGES_ESPAIS, $pdo),
        qi(Tables::DB_CIUTATS, $pdo),
        qi(Tables::DB_VIATGES_ESPAIS_TIPUS, $pdo),
        qi(Tables::DB_IMATGES, $pdo),
    );

    try {

        $params = [':slug' => $espai];
        $result = $db->getData($query, $params);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // 7. Detalls fitxa Viatge
    // ruta GET => "/api/viatges/get/?fitxaViatgeDetalls=perpinya"
} else if (isset($_GET['fitxaViatgeDetalls'])) {
    $slug = $_GET['fitxaViatgeDetalls'];

    $query = "SELECT v.id, v.viatge, v.slug, v.dateCreated, v.dateModified, c.pais_cat, i.nameImg, i.alt, v.dataInici, v.dataFi, v.descripcio
    FROM db_viatges_llistat AS v
    INNER JOIN db_countries AS c ON c.id = v.pais
    LEFT JOIN db_img AS i ON v.img = i.id
    WHERE v.slug = :slug";
}
