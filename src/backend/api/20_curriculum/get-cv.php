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
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
