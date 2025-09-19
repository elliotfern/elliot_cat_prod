<?php

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;

$slug = $routeParams[0];
$db = new Database();
$pdo = $db->getPdo();

/*
 * BACKEND DB CURRICULUM
 * FUNCIONS
 * @
 */

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

// GET : Perfil CV ID
// URL: https://elliot.cat/api/curriculum/get/perfilCV?id=44
if ($slug === "perfilCV") {

    $id = $_GET['id'] ?? null;
    $sql = <<<SQL
            SELECT c.id, c.email, c.nom_complet, c.tel, c.web, c.adreca, ci.ciutat_ca, i.nameImg, c.disponibilitat, c.visibilitat, c.created_at, c.updated_at, co.pais_ca, c.img_perfil, uuid_bin_to_text(c.localitzacio_ciutat) AS localitzacio_ciutat
            FROM %s AS c
            LEFT JOIN %s AS i ON c.img_perfil = i.id
            LEFT JOIN %s AS ci ON c.localitzacio_ciutat = ci.id
            LEFT JOIN %s AS co ON ci.pais_id = co.id
            WHERE c.id = :id
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CURRICULUM_PERFIL, $pdo),
        qi(Tables::DB_IMATGES, $pdo),
        qi(Tables::DB_CIUTATS, $pdo),
        qi(Tables::DB_PAISOS, $pdo)
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

    // GET : Perfil CV i18n ID
    // URL: https://elliot.cat/api/curriculum/get/perfilCVI18n?perfil_id=1&locale=1
} else if ($slug === "perfilCVi18n") {

    $perfilId = 1;
    $locale   = isset($_GET['locale']) ? (int)$_GET['locale'] : null;

    $db = new Database();

    $sql = <<<SQL
            SELECT id, perfil_id, locale, titular, sumari
            FROM %s
            WHERE perfil_id = :perfil_id AND locale = :locale
            LIMIT 1
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CURRICULUM_PERFIL_I18N, $pdo)
    );

    try {
        $params = [':perfil_id' => $perfilId, ':locale' => $locale];
        $row = $db->getData($query, $params, true);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(MissatgesAPI::success('get'), $row, 200);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : Links ID
    // URL: https://elliot.cat/api/curriculum/get/linkCV?id=1
} else if ($slug === "linkCV") {

    $id   = isset($_GET['id']) ? (int)$_GET['id'] : null;

    $sql = <<<SQL
            SELECT id, perfil_id, label, url, posicio, visible 	
            FROM %s
            WHERE id = :id
            LIMIT 1
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CURRICULUM_LINKS, $pdo)
    );

    try {
        $params = [':id' => $id, ':id' => $id];
        $row = $db->getData($query, $params, true);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(MissatgesAPI::success('get'), $row, 200);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }


    // GET : Links cv
    // URL: https://elliot.cat/api/curriculum/get/linksCV
} else if ($slug === "linksCV") {

    $sql = <<<SQL
              SELECT l.id, l.perfil_id, l.label, l.url, l.posicio, l.visible, l.icon_id, i.nameImg
              FROM %s AS l
              LEFT JOIN %s AS i ON l.icon_id = i.id
              ORDER BY l.posicio
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CURRICULUM_LINKS, $pdo),
        qi(Tables::DB_IMATGES, $pdo)
    );

    try {
        $row = $db->getData($query);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(MissatgesAPI::success('get'), $row, 200);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : Habilitat ID
    // URL: https://elliot.cat/api/curriculum/get/habilitatId?id=1
} else if ($slug === "habilitatId") {

    $id   = isset($_GET['id']) ? (int)$_GET['id'] : null;

    $sql = <<<SQL
              SELECT id, nom, imatge_id, posicio 	
              FROM %s
              WHERE id = :id
              LIMIT 1
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CURRICULUN_HABILITATS, $pdo)
    );

    try {
        $params = [':id' => $id, ':id' => $id];
        $row = $db->getData($query, $params, true);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(MissatgesAPI::success('get'), $row, 200);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : Habilitats
    // URL: https://elliot.cat/api/curriculum/get/habilitats
} else if ($slug === "habilitats") {

    $sql = <<<SQL
              SELECT id, nom, imatge_id, posicio 	
              FROM %s
              WHERE id = :id
              LIMIT 1
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CURRICULUN_HABILITATS, $pdo)
    );

    $query = "SELECT h.id, h.nom, h.imatge_id, h.posicio, i.nameImg
              FROM db_curriculum_habilitats AS h
              LEFT JOIN db_img AS i ON h.imatge_id = i.id
              ORDER BY h.posicio";

    try {
        $row = $db->getData($query);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(MissatgesAPI::success('get'), $row, 200);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : Experiencia ID
    // URL: https://elliot.cat/api/curriculum/get/experienciaId?id=1
} else if ($slug === "experienciaId") {

    $id   = isset($_GET['id']) ? (int)$_GET['id'] : null;

    $sql = <<<SQL
              SELECT e.id, e.empresa, e.empresa_url, uuid_bin_to_text(e.empresa_localitzacio) AS empresa_localitzacio, e.data_inici, e.data_fi, e.is_current, e.logo_empresa, e.posicio, e.visible, e.created_at, e.updated_at, i.nameImg, c.ciutat_ca AS city, co.pais_ca AS pais_cat
              FROM %s AS e
              LEFT JOIN %s AS i ON e.logo_empresa = i.id
              LEFT JOIN %s AS c ON e.empresa_localitzacio = c.id
              LEFT JOIN %s AS co ON c.pais_id = co.id
              WHERE e.id = :id
              LIMIT 1
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CURRICULUM_EXPERIENCIA_PROFESSIONAL, $pdo),
        qi(Tables::DB_IMATGES, $pdo),
        qi(Tables::DB_CIUTATS, $pdo),
        qi(Tables::DB_PAISOS, $pdo)
    );

    try {
        $params = [':id' => $id, ':id' => $id];
        $row = $db->getData($query, $params, true);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(MissatgesAPI::success('get'), $row, 200);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : llistat Experiencies
    // URL: https://elliot.cat/api/curriculum/get/experiencies
} else if ($slug === "experiencies") {

    $sql = <<<SQL
              SELECT e.id, e.empresa, e.empresa_url, uuid_bin_to_text(e.empresa_localitzacio) AS empresa_localitzacio, e.data_inici, e.data_fi, e.is_current, e.logo_empresa, e.posicio, e.visible, e.created_at, e.updated_at, i.nameImg, c.ciutat_ca AS city, co.pais_ca AS pais_cat
              FROM %s AS e
              LEFT JOIN %s AS i ON e.logo_empresa = i.id
              LEFT JOIN %s AS c ON e.empresa_localitzacio = c.id
              LEFT JOIN %s AS co ON c.pais_id = co.id
              ORDER BY e.posicio ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CURRICULUM_EXPERIENCIA_PROFESSIONAL, $pdo),
        qi(Tables::DB_IMATGES, $pdo),
        qi(Tables::DB_CIUTATS, $pdo),
        qi(Tables::DB_PAISOS, $pdo)
    );

    try {
        $row = $db->getData($query);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(MissatgesAPI::success('get'), $row, 200);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }
} else if ($slug === "experienciaDetall") {
    $id = $_GET['id'] ?? null;
    if (!$id || !is_numeric($id)) {
        Response::error(MissatgesAPI::error('validacio'), ['id requerit'], 400);
    }

    try {
        /** @var PDO $conn */
        // 1. Datos principales

        $sql = <<<SQL
             SELECT e.id, e.empresa, e.empresa_url, e.empresa_localitzacio, e.data_inici, e.data_fi, e.is_current, e.logo_empresa, e.posicio, e.visible, e.created_at, e.updated_at, i.nameImg, c.ciutat_ca AS city, co.pais_ca AS pais_cat
                    FROM %s e
                    LEFT JOIN %s AS i ON e.logo_empresa = i.id
                    LEFT JOIN %s AS c ON e.empresa_localitzacio = c.id
                    LEFT JOIN %s AS co ON c.pais_id = co.id
                    WHERE e.id = :id
                    LIMIT 1
            SQL;

        $query = sprintf(
            $sql,
            qi(Tables::CURRICULUM_EXPERIENCIA_PROFESSIONAL, $pdo),
            qi(Tables::DB_IMATGES, $pdo),
            qi(Tables::DB_CIUTATS, $pdo),
            qi(Tables::DB_PAISOS, $pdo)
        );

        $stmtMain = $conn->prepare($query);
        $stmtMain->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmtMain->execute();
        $main = $stmtMain->fetch(PDO::FETCH_ASSOC);

        if (!$main) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
        }

        // 2. Traducciones (i18n)

        $sql = <<<SQL
                SELECT i.id AS idi18n, i.experiencia_id, i.locale, i.rol_titol, i.sumari, i.fites
                FROM %s i
                WHERE i.experiencia_id = :id
                ORDER BY i.locale ASC
            SQL;

        $query = sprintf(
            $sql,
            qi(Tables::CURRICULUM_EXPERIENCIA_PROFESSIONAL_I18N, $pdo)
        );

        $stmtI18n = $conn->prepare($query);
        $stmtI18n->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmtI18n->execute();
        $i18nRows = $stmtI18n->fetchAll(PDO::FETCH_ASSOC);

        // 3. Combinamos
        $result = $main;
        $result['i18n'] = $i18nRows;

        Response::success(
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }
} else if ($slug === "experienciaIdi18n") {
    $id = $_GET['id'] ?? null;

    $sql = <<<SQL
                SELECT i.id, i.experiencia_id, i.locale, i.rol_titol, i.sumari, i.fites
                FROM %s AS i
                WHERE i.id = :id
                LIMIT 1
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CURRICULUM_EXPERIENCIA_PROFESSIONAL_I18N, $pdo)
    );

    try {

        $params = [':id' => $id, ':id' => $id];
        $row = $db->getData($query, $params, true);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(MissatgesAPI::success('get'), $row, 200);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }
} else if ($slug === "educacioId") {
    $id = $_GET['id'] ?? null;

    $sql = <<<SQL
                SELECT e.id, e.institucio, e.institucio_url, uuid_bin_to_text(e.institucio_localitzacio) AS institucio_localitzacio, e.data_inici, e.data_fi, e.logo_id, e.posicio, e.visible, i.nameImg, c.ciutat_ca, co.pais_ca
                FROM %s AS e
                LEFT JOIN %s AS i ON e.logo_id = i.id
                LEFT JOIN %s AS c ON e.institucio_localitzacio = c.id
                LEFT JOIN %s AS co ON c.pais_id = co.id
                WHERE e.id = :id
                LIMIT 1
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CURRICULUM_EDUCACIO, $pdo),
        qi(Tables::DB_IMATGES, $pdo),
        qi(Tables::DB_CIUTATS, $pdo),
        qi(Tables::DB_PAISOS, $pdo)
    );

    try {

        $params = [':id' => $id, ':id' => $id];
        $row = $db->getData($query, $params, true);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(MissatgesAPI::success('get'), $row, 200);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }
} else if ($slug === "llistatEducacio") {

    $sql = <<<SQL
            SELECT e.id, e.institucio, e.institucio_url, uuid_bin_to_text(e.institucio_localitzacio) AS institucio_localitzacio, e.data_inici, e.data_fi, e.logo_id, e.posicio, e.visible, i.nameImg, c.ciutat_ca, co.pais_ca
            FROM %s AS e
            LEFT JOIN %s AS i ON e.logo_id = i.id
            LEFT JOIN %s AS c ON e.institucio_localitzacio = c.id
            LEFT JOIN %s AS co ON c.pais_id = co.id
            ORDER BY e.posicio ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CURRICULUM_EDUCACIO, $pdo),
        qi(Tables::DB_IMATGES, $pdo),
        qi(Tables::DB_CIUTATS, $pdo),
        qi(Tables::DB_PAISOS, $pdo)
    );

    try {

        $row = $db->getData($query);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(MissatgesAPI::success('get'), $row, 200);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }
} else if ($slug === "educacioI18nId") {
    $id = $_GET['id'] ?? null;

    $sql = <<<SQL
                SELECT 
                id, educacio_id, locale, grau, notes
                FROM %s AS e
                WHERE e.id = :id
                LIMIT 1
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::CURRICULUM_EDUCACIO_I18N, $pdo)
    );

    try {
        $params = [':id' => $id, ':id' => $id];
        $row = $db->getData($query, $params, true);

        if (empty($row)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        Response::success(MissatgesAPI::success('get'), $row, 200);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }
} else if ($slug === "educacioI18nDetallId") {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['Falta el paràmetre id'],
            400
        );
        exit();
    }

    try {
        // --- 1. Dades principals ---
        $sqlMain = <<<SQL
            SELECT 
                        e.id,
                        e.institucio,
                        e.institucio_url,
                        e.institucio_localitzacio,
                        e.data_inici,
                        e.data_fi,
                        e.logo_id,
                        e.posicio,
                        e.visible,
                        i.nameImg,
                        c.ciutat_ca,
                        co.pais_ca
            FROM %s AS e
            LEFT JOIN %s AS i ON e.logo_id = i.id
            LEFT JOIN %s AS c ON e.institucio_localitzacio = c.id
            LEFT JOIN %s AS co ON c.pais_id = co.id 
            WHERE e.id = :id
            LIMIT 1
        SQL;

        $sql = sprintf(
            $sqlMain,
            qi(Tables::CURRICULUM_EDUCACIO, $pdo),
            qi(Tables::DB_IMATGES, $pdo),
            qi(Tables::DB_CIUTATS, $pdo),
            qi(Tables::DB_PAISOS, $pdo)
        );

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $main = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$main) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            exit();
        }

        // --- 2. Traduccions ---

        $sql = <<<SQL
                SELECT 
                id, educacio_id, locale, grau, notes
                FROM %s
                WHERE educacio_id = :id
                ORDER BY locale ASC
            SQL;

        $query = sprintf(
            $sql,
            qi(Tables::CURRICULUM_EDUCACIO_I18N, $pdo)
        );

        $stmt2 = $conn->prepare($query);
        $stmt2->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt2->execute();
        $i18n = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $main['i18n'] = $i18n;

        Response::success(
            MissatgesAPI::success('get'),
            $main,
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
