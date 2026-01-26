<?php

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");

$allowed_origins = array("https://elliot.cat", "https://historiaoberta.cat");

// Verificar si la URL de referencia existe
if (isset($_SERVER['HTTP_REFERER'])) {
    // Obtener la URL de referencia
    $url = $_SERVER['HTTP_REFERER'];

    // Parsear la URL para obtener solo la parte de dominio
    $parsed_url = parse_url($url);

    // Verificar si el esquema y el host están disponibles
    if (isset($parsed_url['scheme']) && isset($parsed_url['host'])) {
        // Extraer la parte del dominio y añadir el esquema
        $base_url = $parsed_url['scheme'] . "://" . $parsed_url['host'];

        // Eliminar todo lo que sigue después de .cat/
        $base_url = preg_replace('/(https:\/\/[^\/]+\/[^\/]+)\/.*/', '$1', $base_url);
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Acceso no permitido']);
        exit;
    }
} else {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no permitido']);
    exit;
}

if (isset($base_url) && in_array($base_url, $allowed_origins)) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_REFERER']}");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no permitido']);
    exit;
}

// Check if the request method is OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// 1. Visites realizades en un espai
// ruta GET => "/api/viatges/get/?llistatVisitesEspai"
if (isset($_GET['llistatVisitesEspai'])) {
    $slug = $_GET['llistatVisitesEspai'];

    $query = "SELECT v.id, vl.slug, vl.viatge AS nom, v.dataVisita AS any1
    FROM db_travel_places_visited AS v
    INNER JOIN db_viatges_llistat AS vl ON v.idViatge = vl.id
    INNER JOIN db_travel_places AS p ON v.espId = p.id
    WHERE p.slug = :slug
    ORDER BY v.dataVisita ASC";

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
} else if (isset($_GET['llistatViatges'])) {

    $query = "SELECT l.id, l.viatge, l.descripcio, l.dataInici, l.dataFi, l.slug, l.pais, c.pais_cat
            FROM db_viatges_llistat AS l
            LEFT JOIN db_countries AS c ON l.pais = c.id
            ORDER BY l.dataInici DESC";

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
