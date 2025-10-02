<?php


use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;

$db = new Database();
$pdo = $db->getPdo();
$slug = $routeParams[0];

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");

// Definir el dominio permitido
$allowedOrigin = APP_DOMAIN;

// Llamar a la función para verificar el referer
checkReferer($allowedOrigin);

// Verificar que el método de la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}


// 1) Llistat categories enllaços
// ruta GET => "/api/links/llistatTemes"
if ($slug === 'llistatTemes') {

    $sql = <<<SQL
            SELECT uuid_bin_to_text(t.id) AS id, tema_ca
            FROM %s AS t
            ORDER BY t.tema_ca ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_TEMES, $pdo),

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

    // 1) Llistat enllaços
    // ruta GET => "/api/links/llistatLinks"
} else if ($slug === 'llistatLinks') {

    $sql = <<<SQL
            SELECT uuid_bin_to_text(l.id) AS id, l.nom, l.web, l.dateCreated, l.dateModified, st.tema_ca, s.sub_tema_ca, t.tipus_ca, i.idioma_ca
            FROM %s AS l
            LEFT JOIN %s AS s ON s.id = l.sub_tema_id
            LEFT JOIN %s AS st ON s.tema_id = st.id
            LEFT JOIN %s AS t ON l.tipus = t.id
            LEFT JOIN %s AS i ON l.lang = i.id
            ORDER BY l.nom ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_LINKS, $pdo),
        qi(Tables::DB_SUBTEMES, $pdo),
        qi(Tables::DB_TEMES, $pdo),
        qi(Tables::DB_LINKS_TIPUS, $pdo),
        qi(Tables::DB_IDIOMES, $pdo),
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

    // 2) Llistat enllaços segons una categoria en concret
    // ruta GET => "/api/adreces/taulaLlistatTemaId?id=11"
} else if ($slug === 'taulaLlistatTemaId') {
    $id = $_GET['id'];

    $sql = <<<SQL
            SELECT uuid_bin_to_text(l.id) AS id, uuid_bin_to_text(l.sub_tema_id) AS sub_tema_id, l.web, l.nom, l.tipus, l.lang, l.dateCreated, l.dateModified, st.sub_tema_ca, t.tema_ca, lt.tipus_ca
            FROM %s AS l
            LEFT JOIN %s AS st ON l.sub_tema_id = st.id
            LEFT JOIN %s AS t ON st.tema_id = t.id
            LEFT JOIN %s AS lt ON l.tipus = lt.id
            WHERE t.id = uuid_text_to_bin(:id)
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_LINKS, $pdo),
        qi(Tables::DB_SUBTEMES, $pdo),
        qi(Tables::DB_TEMES, $pdo),
        qi(Tables::DB_LINKS_TIPUS, $pdo),
    );

    try {

        $params = [':id' => $id];
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

    // 4) Llistat de topics
    // ruta GET => "/api/adreces/get/llistatSubTemes"
} else if ($slug === 'llistatSubTemes') {

    $sql = <<<SQL
            SELECT uuid_bin_to_text(st.id) AS id, uuid_bin_to_text(st.tema_id) AS tema_id, st.sub_tema_ca, st.sub_tema_en, st.sub_tema_es, st.sub_tema_it, st.sub_tema_fr, t.tema_ca
            FROM %s AS st
            LEFT JOIN %s AS t ON st.tema_id = t.id
            ORDER BY st.sub_tema_ca ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_SUBTEMES, $pdo),
        qi(Tables::DB_TEMES, $pdo),

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

    // 5) Ruta para sacar 1 enlace y actualizarlo 
    // ruta GET => "/api/adreces/?linkId=11"
} elseif ($slug === 'linkId') {
    $id = $_GET['id'];

    $sql = <<<SQL
            SELECT uuid_bin_to_text(l.id) AS id, uuid_bin_to_text(l.sub_tema_id) AS sub_tema_id, l.web, l.nom, l.tipus, l.lang
            FROM db_links AS l
            WHERE l.id = uuid_text_to_bin(:id)
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_SUBTEMES, $pdo),
    );

    try {

        $params = [':id' => $id];
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

    // 5) Ruta per treure els enllaços d'un subtema
    // ruta GET => "/api/adreces/subTemaId=11"
} elseif ($slug === 'subTemaId') {
    $id = $_GET['id'];

    $sql = <<<SQL
            SELECT uuid_bin_to_text(l.id) AS id, uuid_bin_to_text(l.sub_tema_id) AS sub_tema_id, l.web, l.nom, l.tipus, l.lang, l.dateCreated, l.dateModified, st.sub_tema_ca, t.tema_ca, lt.tipus_ca
            FROM %s AS l
            LEFT JOIN %s AS st ON l.sub_tema_id = st.id
            LEFT JOIN %s AS t ON st.tema_id = t.id
            LEFT JOIN %s AS lt ON l.tipus = lt.id
            WHERE l.sub_tema_id = uuid_text_to_bin(:id)
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_LINKS, $pdo),
        qi(Tables::DB_SUBTEMES, $pdo),
        qi(Tables::DB_TEMES, $pdo),
        qi(Tables::DB_LINKS_TIPUS, $pdo),
    );


    $sql = <<<SQL
            SELECT uuid_bin_to_text(st.id) AS id, uuid_bin_to_text(st.tema_id) AS tema_id, st.sub_tema_ca, st.sub_tema_en, st.sub_tema_es, st.sub_tema_it, st.sub_tema_fr, t.tema_ca
            FROM %s AS st
            LEFT JOIN %s AS t ON st.tema_id = t.id
            ORDER BY st.sub_tema_ca ASC
            SQL;

    try {

        $params = [':id' => $id];
        $result = $db->getData($query, $params, false);

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
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
