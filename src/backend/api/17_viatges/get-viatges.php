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
                INNER JOIN %s AS vl ON v.viatge_id = vl.id
                INNER JOIN %s AS p ON v.espai_id = p.id
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


    // 3. Fitxa espai
    // ruta GET => "/api/viatges/get/?fitxaEspai=palau-reial"
} else if (isset($_GET['fitxaEspai'])) {
    $slug = $_GET['fitxaEspai'];

    $query = "SELECT p.id, p.nom, p.EspNomCast, p.EspNomEng, p.slug, p.EspNomIt, p.EspFundacio, p.EspDescripcio, p.EspDescripcioCast, p.EspDescripcioEng, p.EspDescripcioIt, p.EspTipus, p.EspWeb, p.idCiutat, c.ciutat, a.TipusNom, p.img AS idImg, i.nom AS img, i.alt, i.nameImg, p.coordinades_longitud, p.coordinades_latitud, p.dateCreated, p.dateModified
    FROM db_travel_places AS p
    INNER JOIN db_cities AS c ON c.id = p.idCiutat
    INNER JOIN db_travel_accommodation_type AS a ON p.EspTipus = a.id
    LEFT JOIN db_img AS i ON p.img = i.id
    WHERE p.slug = :slug";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Devolver los datos en formato JSON
    echo json_encode($data);

    // 4. Imatges espais
    // ruta GET => "/api/viatges/get/?llistatImatgesEspais"
} else if (isset($_GET['llistatImatgesEspais'])) {

    $query = "SELECT i.id, i.nom
    FROM db_img AS i
    WHERE i.typeImg = 17";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los datos en formato JSON
    echo json_encode($data);

    // 4. Imatges espais
    // ruta GET => "/api/viatges/get/?llistatTipusEspais"
} else if (isset($_GET['llistatTipusEspais'])) {

    $query = "SELECT t.id, t.TipusNom
    FROM db_travel_accommodation_type AS t";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los datos en formato JSON
    echo json_encode($data);

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

    // Preparar la consulta
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los datos en formato JSON
    echo json_encode($data);

    // 7. Detalls fitxa espai
    // ruta GET => "/api/viatges/get/?fitxaEspaiDetalls=perpinya"
} else if (isset($_GET['fitxaEspaiDetalls'])) {
    $slug = $_GET['fitxaEspaiDetalls'];

    $query = "SELECT p.id, p.nom, p.EspNomCast, p.EspNomEng, p.EspNomIt, p.EspFundacio, p.EspDescripcio, p.EspDescripcioCast, p.EspDescripcioEng, p.EspDescripcioIt, p.EspTipus, p.EspWeb, p.idCiutat, c.ciutat, a.TipusNom, i.nom AS img, i.alt, i.nameImg, p.coordinades_longitud, p.coordinades_latitud, p.dateCreated, p.dateModified
    FROM db_travel_places AS p
    INNER JOIN db_cities AS c ON c.id = p.idCiutat
    INNER JOIN db_travel_accommodation_type AS a ON p.EspTipus = a.id
    LEFT JOIN db_img AS i ON p.img = i.id
    WHERE p.slug = :slug";

    // Preparar la consulta
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Devolver los datos en formato JSON
    echo json_encode($data);

    // 7. Detalls fitxa Viatge
    // ruta GET => "/api/viatges/get/?fitxaViatgeDetalls=perpinya"
} else if (isset($_GET['fitxaViatgeDetalls'])) {
    $slug = $_GET['fitxaViatgeDetalls'];

    $query = "SELECT v.id, v.viatge, v.slug, v.dateCreated, v.dateModified, c.pais_cat, i.nameImg, i.alt, v.dataInici, v.dataFi, v.descripcio
    FROM db_viatges_llistat AS v
    INNER JOIN db_countries AS c ON c.id = v.pais
    LEFT JOIN db_img AS i ON v.img = i.id
    WHERE v.slug = :slug";

    // Preparar la consulta
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Devolver los datos en formato JSON
    echo json_encode($data);
}
