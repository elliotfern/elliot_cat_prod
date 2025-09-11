<?php


use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;

$slug = $routeParams[0];

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

    $db = new Database();
    $query = "SELECT 
	        c.id, c.email, c.nom_complet, c.tel, c.web, c.adreca, ci.city, i.nameImg, c.disponibilitat, c.visibilitat, c.created_at, c.updated_at, co.pais_cat, c.img_perfil, c.localitzacio_ciutat
            FROM db_curriculum_perfil AS c
            INNER JOIN db_img AS i ON c.img_perfil = i.id
            INNER JOIN db_cities AS ci ON c.localitzacio_ciutat = ci.id
            INNER JOIN db_countries AS co ON ci.country = co.id
            WHERE c.id = :id";

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
    $query = "SELECT id, perfil_id, locale, titular, sumari
              FROM db_curriculum_perfil_i18n
              WHERE perfil_id = :perfil_id AND locale = :locale
              LIMIT 1";

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

    $db = new Database();
    $query = "SELECT id, perfil_id, label, url, posicio, visible 	
              FROM db_curriculum_links
              WHERE id = :id
              LIMIT 1";

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

    $db = new Database();
    $query = "SELECT l.id, l.perfil_id, l.label, l.url, l.posicio, l.visible, l.icon_id, i.nameImg
              FROM db_curriculum_links AS l
              LEFT JOIN db_img AS i ON l.icon_id = i.id
              ORDER BY l.posicio";

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

    $db = new Database();
    $query = "SELECT id, nom, imatge_id, posicio 	
              FROM db_curriculum_habilitats
              WHERE id = :id
              LIMIT 1";

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

    $db = new Database();
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

    $db = new Database();
    $query = "SELECT e.id, e.empresa, e.empresa_url, e.empresa_localitzacio, e.data_inici, e.data_fi, e.is_current, e.logo_empresa, e.posicio, e.visible, e.created_at, e.updated_at, i.nameImg, c.city, co.pais_cat
              FROM db_curriculum_experiencia_professional AS e
              LEFT JOIN db_img AS i ON e.logo_empresa = i.id
              LEFT JOIN db_cities AS c ON e.empresa_localitzacio = c.id
              INNER JOIN db_countries AS co ON c.country = co.id
              WHERE e.id = :id
              LIMIT 1";

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

    $db = new Database();
    $query = "SELECT e.id, e.empresa, e.empresa_url, e.empresa_localitzacio, e.data_inici, e.data_fi, e.is_current, e.logo_empresa, e.posicio, e.visible, e.created_at, e.updated_at, i.nameImg, c.city, co.pais_cat
              FROM db_curriculum_experiencia_professional AS e
              LEFT JOIN db_img AS i ON e.logo_empresa = i.id
              LEFT JOIN db_cities AS c ON e.empresa_localitzacio = c.id
              INNER JOIN db_countries AS co ON c.country = co.id
              ORDER BY e.posicio ASC";

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
        $sqlMain = "SELECT 
                        e.id,
                        e.empresa,
                        e.empresa_url,
                        e.empresa_localitzacio,
                        e.data_inici,
                        e.data_fi,
                        e.is_current,
                        e.logo_empresa,
                        e.posicio,
                        e.visible,
                        e.created_at,
                        e.updated_at,
                        i.nameImg, 
                        c.city, 
                        co.pais_cat
                    FROM db_curriculum_experiencia_professional e
                    LEFT JOIN db_img AS i ON e.logo_empresa = i.id
                    LEFT JOIN db_cities AS c ON e.empresa_localitzacio = c.id
                    LEFT JOIN db_countries AS co ON c.country = co.id
                    WHERE e.id = :id
                    LIMIT 1";
        $stmtMain = $conn->prepare($sqlMain);
        $stmtMain->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmtMain->execute();
        $main = $stmtMain->fetch(PDO::FETCH_ASSOC);

        if (!$main) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
        }

        // 2. Traducciones (i18n)
        $sqlI18n = "SELECT 
                        i.id AS idi18n,
                        i.experiencia_id,
                        i.locale,
                        i.rol_titol,
                        i.sumari,
                        i.fites
                    FROM db_curriculum_experiencia_professional_i18n i
                    WHERE i.experiencia_id = :id
                    ORDER BY i.locale ASC";
        $stmtI18n = $conn->prepare($sqlI18n);
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
    $db = new Database();

    try {
        $query = "SELECT 
                        i.id,
                        i.experiencia_id,
                        i.locale,
                        i.rol_titol,
                        i.sumari,
                        i.fites
                    FROM db_curriculum_experiencia_professional_i18n AS i
                    WHERE i.id = :id
                    LIMIT 1";

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
    $db = new Database();

    try {
        $query = "SELECT 
                    e.id, e.institucio, e.institucio_url, e.institucio_localitzacio, e.data_inici, e.data_fi, e.logo_id, e.posicio, e.visible, i.nameImg, c.city, co.pais_cat
                    FROM db_curriculum_educacio AS e
                    LEFT JOIN db_img AS i ON e.logo_id = i.id
                    LEFT JOIN db_cities AS c ON e.institucio_localitzacio = c.id
                    LEFT JOIN db_countries AS co ON c.country = co.id
                    WHERE e.id = :id
                    LIMIT 1";

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

    $db = new Database();

    try {
        $query = "SELECT 
                    e.id, e.institucio, e.institucio_url, e.institucio_localitzacio, e.data_inici, e.data_fi, e.logo_id, e.posicio, e.visible, i.nameImg, c.city, co.pais_cat
                    FROM db_curriculum_educacio AS e
                    LEFT JOIN db_img AS i ON e.logo_id = i.id
                    LEFT JOIN db_cities AS c ON e.institucio_localitzacio = c.id
                    LEFT JOIN db_countries AS co ON c.country = co.id
                    ORDER BY e.posicio ASC";

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
    $db = new Database();

    try {
        $query = "SELECT 
                    id, educacio_id, locale, grau, notes
                    FROM  db_curriculum_educacio_i18n  AS e
                    WHERE e.id = :id
                    LIMIT 1";

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
        $sqlMain = "SELECT 
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
                        c.city,
                        co.pais_cat
                    FROM db_curriculum_educacio e
                    LEFT JOIN db_img AS i ON e.logo_id = i.id
                    LEFT JOIN db_cities AS c ON e.institucio_localitzacio = c.id
                    LEFT JOIN db_countries AS co ON c.country = co.id
                    WHERE e.id = :id
                    LIMIT 1";

        $stmt = $conn->prepare($sqlMain);
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
        $sqlI18n = "SELECT 
                        id,
                        educacio_id,
                        locale,
                        grau,
                        notes
                    FROM db_curriculum_educacio_i18n
                    WHERE educacio_id = :id
                    ORDER BY locale ASC";

        $stmt2 = $conn->prepare($sqlI18n);
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
