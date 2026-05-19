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

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Metode no permès']);
    exit();
}

// 1) Llistat categories enllaços
// ruta GET => "/api/links/llistatTemes"
if ($slug === 'llistatTemes') {

    $sql = <<<SQL
            SELECT t.id, tema
            FROM %s AS t
            ORDER BY t.tema ASC
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

    // 1) Llistat enllaços
    // ruta GET => "/api/links/llistatLinks"
} else if ($slug === 'llistatLinks') {

    $sql = <<<SQL
            SELECT l.id, l.nom, l.web, l.dateCreated, l.dateModified, st.tema, s.sub_tema, t.tipus, i.idioma_ca
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

    // 2) Llistat enllaços segons una categoria en concret
    // ruta GET => "/api/adreces/llistatLinksTemaId?id=11"
} else if ($slug === 'llistatLinksTemaId') {
    $id = $_GET['id'];
    $idBin = !empty($id) ? uuid::toBinary($id) : null;

    $sql = <<<SQL
            SELECT st.id, st.sub_tema, t.tema
            FROM %s AS st
            LEFT JOIN %s AS t ON st.tema_id = t.id
            WHERE t.id = :id
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_SUBTEMES, $pdo),
        qi(Tables::DB_TEMES, $pdo),
    );

    try {

        $params = [':id' => $idBin];
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

    // 4) Llistat de topics
    // ruta GET => "/api/adreces/get/llistatSubTemes"
} else if ($slug === 'llistatSubTemes') {

    $sql = <<<SQL
            SELECT st.id, st.tema_id, st.sub_tema, t.tema
            FROM %s AS st
            LEFT JOIN %s AS t ON st.tema_id = t.id
            ORDER BY st.sub_tema ASC
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

    // 5) Ruta para sacar 1 enlace y actualizarlo 
    // ruta GET => "/api/adreces/?linkId=11"
} elseif ($slug === 'linkId') {
    $id = $_GET['id'];

    $sql = <<<SQL
            SELECT l.id, l.sub_tema_id, l.web, l.nom, l.tipus, l.lang
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

    // 5) Ruta per treure els enllaços d'un subtema
    // ruta GET => "/api/adreces/subTemaId=11"
} elseif ($slug === 'subTemaId') {
    $id = $_GET['id'];

    $sql = <<<SQL
            SELECT l.id, l.sub_tema_id, l.web, l.nom, l.tipus, l.lang, l.dateCreated, l.dateModified, st.sub_tema, t.tema, lt.tipus
            FROM %s AS l
            LEFT JOIN %s AS st ON l.sub_tema_id = st.id
            LEFT JOIN %s AS t ON st.tema_id = t.id
            LEFT JOIN %s AS lt ON l.tipus = lt.id
            WHERE l.sub_tema_id = uuid_text_to_bin(:id)
            ORDER BY l.nom ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_LINKS, $pdo),
        qi(Tables::DB_SUBTEMES, $pdo),
        qi(Tables::DB_TEMES, $pdo),
        qi(Tables::DB_LINKS_TIPUS, $pdo),
    );


    $sql = <<<SQL
            SELECT st.id, st.tema_id, st.sub_tema, t.tema
            FROM %s AS st
            LEFT JOIN %s AS t ON st.tema_id = t.id
            ORDER BY st.sub_tema ASC
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

    // 5) Ruta per modificar un subTema
    // ruta GET => "/api/adreces/detallsSubTemaId=11"
} elseif ($slug === 'detallsSubTemaId') {
    $id = $_GET['id'];

    $sql = <<<SQL
            SELECT st.id, st.tema_id, st.sub_tema
            FROM %s AS st
            WHERE st.id = uuid_text_to_bin(:id)
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
