<?php

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;

$slug = $routeParams[0];

/*
 * BACKEND DB AUXILIARS
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


// 1) AUXILIARS
// Llistat directors
// ruta GET => "/api/auxiliars/get/?type=directors"
if (isset($_GET['type']) && $_GET['type'] == 'directors') {
    global $conn;
    $data = array();
    $stmt = $conn->prepare("SELECT d.id, CONCAT(d.cognoms, ', ', d.nom) AS nomComplet
            FROM 11_aux_cinema_directors AS d
            ORDER BY d.cognoms ASC");
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // Llistat productors
    // ruta GET => "/api/cinema/get/auxiliars/?type=productores"
} elseif (isset($_GET['type']) && $_GET['type'] == 'productores') {
    global $conn;
    $data = array();
    $stmt = $conn->prepare("SELECT p.id, p.productora
            FROM 11_aux_cinema_productores AS p
            ORDER BY p.productora ASC");
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // Llistat imatges pelicules
    // ruta GET => "/api/cinema/get/auxiliars/?type=imgPelis"
} elseif (isset($_GET['type']) && $_GET['type'] == 'imgPelis') {
    global $conn;
    $data = array();
    $stmt = $conn->prepare("SELECT i.id, i.alt
            FROM db_img AS i
            WHERE i.typeImg = 8
            ORDER BY i.alt ASC");
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // Llistat imatges series
    // ruta GET => "/api/cinema/get/auxiliars/?type=imgSeries"
} elseif (isset($_GET['type']) && $_GET['type'] == 'imgSeries') {
    global $conn;
    $data = array();
    $stmt = $conn->prepare("SELECT i.id, i.alt
            FROM db_img AS i
            WHERE i.typeImg = 7
            ORDER BY i.alt ASC");
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // Llistat generes pelicules
    // ruta GET => "/api/cinema/get/auxiliars/?type=generesPelis"
} elseif (isset($_GET['type']) && $_GET['type'] == 'generesPelis') {
    global $conn;
    $data = array();
    $stmt = $conn->prepare("SELECT g.id, g.genere_ca
            FROM 11_aux_cinema_generes AS g
            ORDER BY g.genere_ca ASC");
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);

    // Llistat idiomes pelicules
    // ruta GET => "/api/cinema/get/auxiliars/?type=llengues"
} else if ($slug === "llengues") {

    $db = new Database();
    $query = "SELECT i.id, i.idioma_ca
            FROM aux_idiomes AS i
            ORDER BY i.idioma_ca ASC";

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


    // Llistat paisos
    // ruta GET => "/api/cinema/get/auxiliars/?type=paisos"
} elseif (isset($_GET['type']) && $_GET['type'] == 'paisos') {
    global $conn;
    $data = array();
    $stmt = $conn->prepare("SELECT p.id, p.pais_cat
            FROM db_countries AS p
            ORDER BY p.pais_cat ASC");
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    echo json_encode($data);


    // Llistat complet imatges
    // ruta GET => "/api/auxiliars/get/?llistatCompletImatges"
} else if (isset($_GET['llistatCompletImatges'])) {

    $query = "SELECT i.id, i.typeImg, i.nom, t.name, i.dateCreated, i.nameImg
            FROM db_img AS i
            LEFT JOIN db_img_type AS t ON i.typeImg = t.id
            ORDER BY i.nom ASC";

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

    // GET : llistat imatges usuaris
    // URL: https://elliot.cat/api/auxiliars/get/imatgesUsuaris
} else if ($slug === "imatgesUsuaris") {

    $db = new Database();
    $query = "SELECT 
	      	i.id, i.nameImg, i.nom
            FROM db_img AS i
            WHERE i.typeImg = 18
            ORDER BY i.nom ASC";

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

    // GET : llistat ciutats
    // URL: https://elliot.cat/api/auxiliars/get/ciutats
} else if ($slug === "ciutats") {

    $db = new Database();
    $query = "SELECT 
	      	c.id, c.city
            FROM db_cities AS c
            ORDER BY c.city ASC";

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

    // GET : llistat perfils cv
    // URL: https://elliot.cat/api/auxiliars/get/perfilsCV
} else if ($slug === "perfilsCV") {

    $db = new Database();
    $query = "SELECT p.id, p.nom_complet
            FROM db_curriculum_perfil AS p
            ORDER BY p.nom_complet ASC";

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
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
