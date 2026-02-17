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

// GET : "https://elliot.cat/api/historia-oberta/get/?type=llistat-articles"
if (isset($_GET['type']) && $_GET['type'] == 'llistat-articles') {

    $query = "SELECT a.post_name, DATE(a.post_date) AS postData, a.ID, a.post_title
            FROM epgylzqu_historia_web.xfr_posts AS a
            ORDER BY a.id";

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

    // 2. Llistat de càrrecs d'una persona
    // ruta GET => "/api/historia/get/?carrecsPersona=234"
} else if (isset($_GET['carrecsPersona'])) {
    $id = $_GET['carrecsPersona'];

    $query = "SELECT c.id, c.carrecNom AS carrec, c.carrecInici AS anys, c.carrecFi, o.nomOrg AS organitzacio, o.slug
    FROM aux_persones_carrecs AS c
    LEFT JOIN db_historia_organitzacions AS o ON c.idOrg = o.id
    WHERE c.idPersona = :id
    ORDER BY c.carrecInici";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

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

    // 3. Llistat d'esdeveniments vinculats a d'una persona
    // ruta GET => "/api/historia/get/?esdevenimentsPersona=234"
} else if (isset($_GET['esdevenimentsPersona'])) {
    $id = $_GET['esdevenimentsPersona'];

    $query = "SELECT ep.id AS idEP, e.id, e.esdeNom AS esdeveniment, e.esdeDataIAny AS any, e.slug
    FROM db_historia_esdeveniment_persones AS ep
    INNER JOIN db_historia_esdeveniments AS e ON ep.idEsdev = e.id
    WHERE ep.idPersona = :id
    ORDER BY e.esdeDataIAny ASC";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

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

    // 2. Llistat d'esdeveniments
    // ruta GET => "/api/historia/get/?llistatEsdeveniments"
} else if (isset($_GET['llistatEsdeveniments'])) {
    // Obtener los parámetros de filtro
    $etapaFiltro = isset($_GET['etapa']) ? $_GET['etapa'] : '';
    $subetapaFiltro = isset($_GET['subetapa']) ? $_GET['subetapa'] : '';

    $query = "SELECT e.id, esdeNom, slug, esdeDataIDia, esdeDataIMes, esdeDataIAny, esdeDataFDia, esdeDataFMes, esdeDataFAny, s.nomSubEtapa, p.etapaNom, c.ciutat, co.pais_ca
    FROM db_historia_esdeveniments AS e
    LEFT JOIN db_historia_sub_periode AS s ON e.esSubEtapa = s.id
    LEFT JOIN db_historia_periode_historic AS p ON s.idEtapa = p.id
    LEFT JOIN db_cities AS c ON e.esdeCiutat = c.id
    LEFT JOIN db_countries AS co ON c.country = co.id
    WHERE 1";

    if ($etapaFiltro) {
        $query .= " AND p.id = :etapa";
    }

    if ($subetapaFiltro) {
        $query .= " AND s.id = :subetapa";
    }

    // Eliminar la parte LIMIT y OFFSET
    $query .= " ORDER BY e.esdeDataIAny ASC";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Vincular parámetros si es necesario
    if ($etapaFiltro) {
        $stmt->bindParam(':etapa', $etapaFiltro, PDO::PARAM_INT);
    }
    if ($subetapaFiltro) {
        $stmt->bindParam(':subetapa', $subetapaFiltro, PDO::PARAM_INT);
    }

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los datos y el total de eventos
    echo json_encode([
        'data' => $data,
    ]);

    // 2. Llistat de subetapes
    // ruta GET => "/api/historia/get/?subEtapesEtapa=1"
} else if (isset($_GET['subEtapesEtapa'])) {
    $id = $_GET['subEtapesEtapa'];

    $query = "SELECT s.id, s.nomSubEtapa
    FROM db_historia_sub_periode AS s
    WHERE s.idEtapa = :id
    ORDER BY s.anyInici";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

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

    // 5. Llistat subetapes
} else if (isset($_GET['llistatSubEtapes'])) {

    $query = "SELECT s.id, CONCAT(s.anyInici, '-',  LEFT(s.nomSubEtapa, 30)) AS nomSubEtapa
    FROM db_historia_sub_periode AS s
    ORDER BY s.anyInici ASC";

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

    // 4. Esdeveniment
    // ruta GET => "/api/historia/get/?esdeveniment=revolucio-vellut"
} else if (isset($_GET['esdeveniment'])) {
    $slug = $_GET['esdeveniment'];

    $query = "SELECT e.id, e.esdeNom, e.esdeNomCast, e.esdeNomEng, e.esdeNomIt, e.slug, e.esdeDataIDia, e.esdeDataIMes, e.esdeDataIAny, e.esdeDataFDia, e.esdeDataFMes, e.esdeDataFAny, e.esSubEtapa, e.esdeCiutat, e.dateCreated, e.dateModified, s.nomSubEtapa, p.etapaNom, c.ciutat, co.pais_ca, e.img, i.nameImg, e.descripcio, i.alt
    FROM db_historia_esdeveniments AS e
    LEFT JOIN db_historia_sub_periode AS s ON e.esSubEtapa = s.id
    LEFT JOIN db_historia_periode_historic AS p ON s.idEtapa = p.id
    LEFT JOIN db_cities AS c ON e.esdeCiutat = c.id
    LEFT JOIN db_countries AS co ON c.country = co.id
    LEFT JOIN db_img AS i ON e.img = i.id
    WHERE e.slug = :slug";

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

    // 5. Imatges esdeveniment
    // ruta GET => "/api/historia/get/?llistatImatgesEsdeveniments"
} else if (isset($_GET['llistatImatgesEsdeveniments'])) {

    $query = "SELECT i.id, i.alt
    FROM db_img AS i
    WHERE i.typeImg = 4";

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

    // 3. Llistat de persones vinculades a un esdeveniment
    // ruta GET => "/api/historia/get/?personesEsdeveniments=234"
} else if (isset($_GET['personesEsdeveniments'])) {
    $id = $_GET['personesEsdeveniments'];

    $query = "SELECT e.id, CONCAT(ep.nom, ' ', ep.cognoms) AS nom, ep.slug
    FROM db_historia_esdeveniment_persones AS e
    INNER JOIN db_persones AS ep ON ep.id = e.idPersona
    WHERE e.idEsdev = :id
    ORDER BY ep.cognoms ASC";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

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

    // 3. Llistat d'organitzacions vinculades a un esdeveniment
    // ruta GET => "/api/historia/get/?organitzacionsEsdeveniments=234"
} else if (isset($_GET['organitzacionsEsdeveniments'])) {
    $id = $_GET['organitzacionsEsdeveniments'];

    $query = "SELECT o.id, org.nomOrg AS nom, org.slug
    FROM db_historia_esdeveniment_organitzacio AS o
    INNER JOIN db_historia_organitzacions AS org ON org.id = o.idOrg
    WHERE o.idEsde = :id
    ORDER BY org.nomOrg ASC";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

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

    // 4. Llistat Esdeveniments
    // ruta GET => "/api/historia/get/?llistatEsdevenimentsSelect"
} else if (isset($_GET['llistatEsdevenimentsSelect'])) {

    $query = "SELECT e.id, e.esdeNom
    FROM db_historia_esdeveniments AS e
    ORDER BY e.esdeNom ASC";

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

    // 4. Llistat persones
    // ruta GET => "/api/historia/get/?llistatPersones"
} else if (isset($_GET['llistatPersones'])) {

    $query = "SELECT p.id, CONCAT(p.nom, ' ', p.cognoms) AS nom
    FROM db_persones AS p
    ORDER BY p.cognoms";

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


    // 3. Llistat d'esdeveniments vinculats a una persona
    // ruta GET => "/api/historia/get/?formEsdevenimentsPersona=234"
} else if (isset($_GET['formEsdevenimentsPersona'])) {
    $id = $_GET['formEsdevenimentsPersona'];

    $query = "SELECT e.id, e.idEsdev, e.idPersona
    FROM db_historia_esdeveniment_persones AS e
    WHERE e.id = :id";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

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

    // 3. Llistat organitzacions
    // ruta GET => "/api/historia/get/?llistatOrganitzacions"
} else if (isset($_GET['llistatOrganitzacions'])) {
    $id = $_GET['llistatOrganitzacions'];

    $query = "SELECT o.id, o.nomOrg
    FROM db_historia_organitzacions AS o";

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

    // 3. Relació esdeveniment-organitzacions
    // ruta GET => "/api/historia/get/?formEsdevenimentOrganitzacions"
} else if (isset($_GET['formEsdevenimentOrganitzacions'])) {
    $id = $_GET['formEsdevenimentOrganitzacions'];

    $query = "SELECT o.id, o.idEsde, o.idOrg
    FROM db_historia_esdeveniment_organitzacio AS o
    WHERE o.id = :id";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

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

    // 3. Càrrec d'una persona
    // ruta GET => "/api/historia/get/?personaCarrec="33"
} else if (isset($_GET['personaCarrec'])) {
    $id = $_GET['personaCarrec'];

    $query = "SELECT c.id, c.idPersona, c.carrecNom, c.carrecNomCast, c.carrecNomEng, c.carrecNomIt, c.carrecInici, c.carrecFi, c.idOrg, p.nom, p.cognoms
    FROM aux_persones_carrecs AS c
    INNER JOIN db_persones AS p ON c.idPersona = p.id
    WHERE c.id = :id";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    $stmt->bindParam(':id', $id, PDO::PARAM_STR);

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

    // 3. Llistat organitzacions
    // ruta GET => "/api/historia/get/?paginaOrganitzacions"
} else if (isset($_GET['paginaOrganitzacions'])) {

    $query = "SELECT o.id, o.nomOrg, o.slug, o.orgSig, o.dataFunda, o.dataDiss, c.pais_cat, i.nameImg, o.dateCreated, o.dateModified
    FROM db_historia_organitzacions AS o
    LEFT JOIN db_countries AS c ON o.orgPais = c.id
    LEFT JOIN db_img AS i ON o.img = i.id
    ORDER BY o.dataFunda";

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

    // 3. Pagina detalls organització
    // ruta GET => "/api/historia/get/?fitxaOrganitzacio=primera-internacional"
} else if (isset($_GET['fitxaOrganitzacio'])) {
    $slug = $_GET['fitxaOrganitzacio'];

    $query = "SELECT o.id, o.nomOrg, o.slug, o.orgSig, o.dataFunda, o.dataDiss, ci.ciutat, c.pais_ca, i.nameImg, o.dateCreated, o.dateModified,
    sp.nomSubEtapa, ph.etapaNom, ot.nomTipus, ip.ideologia, i.alt, o.nomOrgCast, o.nomOrgEng, o.nomOrgIt, o.orgPais, o.orgCiutat, o.orgSubEtapa, o.orgTipus, o.orgIdeologia, o.img
    FROM db_historia_organitzacions AS o
    LEFT JOIN db_countries AS c ON o.orgPais = c.id
    LEFT JOIN db_cities AS ci ON o.orgCiutat = ci.id
    LEFT JOIN db_historia_sub_periode AS sp ON o.orgSubEtapa = sp.id
    LEFT JOIN db_historia_periode_historic AS ph ON sp.idEtapa = ph.id
    LEFT JOIN db_historia_organitzacions_tipus AS ot ON o.orgTipus = ot.id
    LEFT JOIN aux_historia_ideologies_politiques AS ip ON o.orgIdeologia = ip.id
    LEFT JOIN db_img AS i ON o.img = i.id
    WHERE o.slug = :slug";

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

    // 3. Pagina Esdeveniments/organitzacio
    // ruta GET => "/api/historia/get/?esdevenimentsOrganitzacio=22"
} else if (isset($_GET['esdevenimentsOrganitzacio'])) {
    $id = $_GET['esdevenimentsOrganitzacio'];

    $query = "SELECT e.esdeNom AS nom, e.slug, e.esdeDataIAny AS any1, e.esdeDataFAny AS any2, o.id
    FROM db_historia_esdeveniment_organitzacio AS o
    LEFT JOIN db_historia_esdeveniments AS e ON o.idEsde = e.id
    WHERE o.idOrg = :id
    ORDER BY e.esdeDataIAny ASC";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

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

    // 3. Pagina càrrecs persones/organitzacio
    // ruta GET => "/api/historia/get/?carrecsPersonesOrganitzacio=22"
} else if (isset($_GET['carrecsPersonesOrganitzacio'])) {
    $id = $_GET['carrecsPersonesOrganitzacio'];

    $query = "SELECT CONCAT(p.nom, ' ', p.cognoms, ' (', c.carrecNom, ')') AS nom, c.carrecInici AS any1, c.carrecFi AS any2, c.id, p.slug
    FROM aux_persones_carrecs AS c
    LEFT JOIN db_persones AS p ON c.idPersona = p.id
    WHERE c.idOrg = :id
    ORDER BY c.carrecInici";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

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

    // 5. Imatges organitzacions
    // ruta GET => "/api/historia/get/?llistatImatgesOrganitzacions"
} else if (isset($_GET['llistatImatgesOrganitzacions'])) {

    $query = "SELECT i.id, i.nom
    FROM db_img AS i
    WHERE i.typeImg = 6
    ORDER BY i.nom DESC";

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


    // 5. Llistat ideologies
    // ruta GET => "/api/historia/get/?llistatIdeologies"
} else if (isset($_GET['llistatIdeologies'])) {

    $query = "SELECT i.id, i.ideologia
    FROM aux_historia_ideologies_politiques AS i
    ORDER BY i.ideologia";

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

    // 5. Tipus organitzacio
    // ruta GET => "/api/historia/get/?llistatTipusOrganitzacio"
} else if (isset($_GET['llistatTipusOrganitzacio'])) {

    $query = "SELECT t.id, t.nomTipus
    FROM db_historia_organitzacions_tipus AS t
    ORDER BY t.nomTipus";

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
} else if ((isset($_GET['type']) && $_GET['type'] == 'blogArticle') && (isset($_GET['paramName']))) {
    global $conn;
    $slug = $_GET['paramName'];
    $data = array();

    // Consulta preparada con parámetros seguros
    $stmt = $conn->prepare(
        "SELECT ID, post_title, post_content, post_date, post_modified, post_name
        FROM epgylzqu_historia_web.xfr_posts
        WHERE post_name = :slug"
    );

    // Asignamos el valor del parámetro de manera segura
    $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
    $stmt->execute();

    // Verificamos si hay filas devueltas
    if ($stmt->rowCount() === 0) {
        echo ('No rows');
    } else {
        while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $users;
        }
        // Convertimos el array a formato JSON
        echo json_encode($data);
    }
} else if (isset($_GET['type']) && $_GET['type'] == 'blog') {
    global $conn;

    $sql = "SELECT p.ID, p.post_title, p.post_content, p.post_date, p.post_name
        FROM epgylzqu_historia_web.xfr_posts AS p
        LEFT JOIN epgylzqu_historia_web.posts_lang AS pl_ca ON p.ID = pl_ca.ca
        LEFT JOIN epgylzqu_historia_web.posts_lang AS pl_es ON p.ID = pl_es.es
        LEFT JOIN epgylzqu_historia_web.posts_lang AS pl_fr ON p.ID = pl_fr.fr
        LEFT JOIN epgylzqu_historia_web.posts_lang AS pl_en ON p.ID = pl_en.en
        LEFT JOIN epgylzqu_historia_web.posts_lang AS pl_it ON p.ID = pl_it.it
        WHERE pl_ca.ca IS NULL
        AND pl_es.es IS NULL
        AND pl_fr.fr IS NULL
        AND pl_en.en IS NULL
        AND pl_it.it IS NULL
        AND p.post_status = 'publish'
        AND p.post_type = 'post'
        ORDER BY p.post_date DESC";

    $stmt = $conn->prepare($sql);

    // Ejecutar la consulta con el parámetro preparado
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
} else if (isset($_GET['type']) && $_GET['type'] == 'llistatCursos' && isset($_GET['langCurso'])) {
    global $conn;
    $slug = $_GET['langCurso'];

    if ($slug === "ca") {
        $sql = "SELECT id,
        ordre,
        nameCa AS nombreCurso,
        resumenCa AS resumen,
        img,
        paramNameCa AS paramName
        FROM db_historia_oberta_cursos 
        ORDER BY ordre ASC";
    } else if ($slug === "en") {
        $sql = "SELECT id,
        ordre,
        nameEn AS nombreCurso,
        resumenEn AS resumen,
        img,
        paramNameEn AS paramName
        FROM db_historia_oberta_cursos 
        ORDER BY ordre ASC";
    } else if ($slug === "es") {
        $sql = "SELECT id,
        ordre,
        nameEs AS nombreCurso,
        resumenEs AS resumen,
        img,
        paramNameEs AS paramName
        FROM db_historia_oberta_cursos 
        ORDER BY ordre ASC";
    } else if ($slug === "fr") {
        $sql = "SELECT id,
        ordre,
        nameFr AS nombreCurso,
        resumenFr AS resumen,
        img,
        paramNameFr AS paramName
        FROM db_historia_oberta_cursos 
        ORDER BY ordre ASC";
    } else if ($slug === "it") {
        $sql = "SELECT id,
        ordre,
        nameIt AS nombreCurso,
        resumenIt AS resumen,
        img,
        paramNameIt AS paramName
        FROM db_historia_oberta_cursos 
        ORDER BY ordre ASC";
    }

    $stmt = $conn->prepare($sql);

    // Ejecutar la consulta con el parámetro preparado
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
} elseif (isset($_GET['type']) && $_GET['type'] == 'curso' && isset($_GET['paramName']) && isset($_GET['langCurso'])) {
    // Aquí puedes obtener los valores de los parámetros
    $paramName = $_GET['paramName'];
    $lang = $_GET['langCurso'];

    if ($lang === "ca") {
        $query = "SELECT p.ID, p.post_title, p.post_date, p.slug, c.nameCa AS courseName, c.descripCa AS courseDescription, c.id AS cursId
        FROM db_historia_oberta_cursos AS c
        LEFT JOIN db_historia_oberta_articles AS l ON c.id = l.curs
        LEFT JOIN db_blog AS p ON p.ID = l.ca
        WHERE c.paramNameCa = :param
        ORDER BY l.ordre ASC";
    } else if ($lang === "en") {
        $query = "SELECT p.ID, p.post_title, p.post_date, p.slug, c.nameEn AS courseName, c.descripEn AS courseDescription, c.id AS cursId
        FROM db_historia_oberta_cursos AS c
        LEFT JOIN db_historia_oberta_articles AS l ON c.id = l.curs
        LEFT JOIN db_blog AS p ON p.ID = l.en
        WHERE c.paramNameEn = :param
        ORDER BY l.ordre ASC;";
    } else if ($lang === "fr") {
        $query = "SELECT p.ID, p.post_title, p.post_date, p.slug, c.nameFr AS courseName, c.descripFr AS courseDescription, c.id AS cursId
        FROM db_historia_oberta_cursos AS c
        LEFT JOIN db_historia_oberta_articles AS l ON c.id = l.curs
        LEFT JOIN db_blog AS p ON p.ID = l.fr
        WHERE c.paramNameFr = :param
        ORDER BY l.ordre ASC;";
    } else if ($lang === "es") {
        $query = "SELECT p.ID, p.post_title, p.post_date, p.slug, c.nameEs AS courseName, c.descripEs AS courseDescription, c.id AS cursId
        FROM db_historia_oberta_cursos AS c
        LEFT JOIN db_historia_oberta_articles AS l ON c.id = l.curs
        LEFT JOIN db_blog AS p ON p.ID = l.es
        WHERE c.paramNameEs = :param
        ORDER BY l.ordre ASC;";
    } else if ($lang === "it") {
        $query = "SELECT p.ID, p.post_title, p.post_date, p.slug, c.nameIt AS courseName, c.descripIt AS courseDescription, c.id AS cursId
        FROM db_historia_oberta_cursos AS c
        LEFT JOIN db_historia_oberta_articles AS l ON c.id = l.curs
        LEFT JOIN db_blog AS p ON p.ID = l.it
        WHERE c.paramNameIt = :param
        ORDER BY l.ordre ASC;";
    }

    global $conn;

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Vincular los parámetros
    $stmt->bindParam(':param', $paramName);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
} elseif (isset($_GET['type']) && $_GET['type'] == 'articleName' && isset($_GET['paramName'])) {

    // Aquí puedes obtener los valores de los parámetros
    $name = $_GET['paramName'];

    global $conn;

    $query = "SELECT p.ID, p.post_title, p.post_date, p.post_content, p.post_excerpt, p.post_modified
        FROM epgylzqu_historia_web.xfr_posts AS p
        WHERE p.post_name = :param";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Vincular los parámetros
    $stmt->bindParam(':param', $name);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar el resultado (solo uno)
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON (como objeto, no array)
    echo json_encode($data);
} elseif (isset($_GET['type']) && $_GET['type'] == 'articleId' && isset($_GET['id'])) {

    // Aquí puedes obtener los valores de los parámetros
    $id = $_GET['id'];

    global $conn;

    $query = "SELECT p.ID, p.post_title, p.post_date, p.post_content, p.post_excerpt, p.post_modified
        FROM epgylzqu_historia_web.xfr_posts AS p
        WHERE p.ID = :param";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Vincular los parámetros
    $stmt->bindParam(':param', $id);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
} elseif (isset($_GET['type']) && $_GET['type'] == 'arxiuArticles' && isset($_GET['lang'])) {

    // Aquí puedes obtener los valores de los parámetros
    $lang = $_GET['lang'];

    global $conn;

    if ($lang === "ca") {
        $query = "SELECT p.ID, p.post_title, p.post_name, c.nameCa AS cursName, c.paramNameCa AS cursParam
            FROM epgylzqu_historia_web.posts_lang AS l
            INNER JOIN epgylzqu_historia_web.xfr_posts AS p ON l.ca = p.ID
            INNER JOIN epgylzqu_elliotfern_intranet.db_openhistory_courses AS c ON l.curs = c.id
            GROUP BY p.id
            ORDER BY l.curs ASC, l.ordre ASC;";
    } else if ($lang === "en") {
        $query = "SELECT p.ID, p.post_title, p.post_name, c.nameEn AS cursName, c.paramNameEn AS cursParam
            FROM epgylzqu_historia_web.posts_lang AS l
            INNER JOIN epgylzqu_historia_web.xfr_posts AS p ON l.en = p.ID
            INNER JOIN epgylzqu_elliotfern_intranet.db_openhistory_courses AS c ON l.curs = c.id
            GROUP BY p.id
            ORDER BY l.curs ASC, l.ordre ASC;";
    } else if ($lang === "fr") {
        $query = "SELECT p.ID, p.post_title, p.post_name, c.nameFr AS cursName, c.paramNameFr AS cursParam
            FROM epgylzqu_historia_web.posts_lang AS l
            INNER JOIN epgylzqu_historia_web.xfr_posts AS p ON l.fr = p.ID
            INNER JOIN epgylzqu_elliotfern_intranet.db_openhistory_courses AS c ON l.curs = c.id
            GROUP BY p.id
            ORDER BY l.curs ASC, l.ordre ASC;";
    } else if ($lang === "es") {
        $query = "SELECT p.ID, p.post_title, p.post_name, c.nameEs AS cursName, c.paramNameEs AS cursParam
            FROM epgylzqu_historia_web.posts_lang AS l
            INNER JOIN epgylzqu_historia_web.xfr_posts AS p ON l.es = p.ID
            INNER JOIN epgylzqu_elliotfern_intranet.db_openhistory_courses AS c ON l.curs = c.id
            GROUP BY p.id
            ORDER BY l.curs ASC, l.ordre ASC;";
    } else if ($lang === "it") {
        $query = "SELECT p.ID, p.post_title, p.post_name, c.nameIt AS cursName, c.paramNameIt AS cursParam
            FROM epgylzqu_historia_web.posts_lang AS l
            INNER JOIN epgylzqu_historia_web.xfr_posts AS p ON l.it = p.ID
            INNER JOIN epgylzqu_elliotfern_intranet.db_openhistory_courses AS c ON l.curs = c.id
            GROUP BY p.id
            ORDER BY l.curs ASC, l.ordre ASC;";
    }

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

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
} elseif (isset($_GET['type']) && $_GET['type'] == 'links' && isset($_GET['lang'])) {

    // Aquí puedes obtener los valores de los parámetros
    $lang = $_GET['lang'];

    global $conn;

    if ($lang === "ca") {
        $query = "SELECT l.id, l.nom, l.web, ca.categoria_ca AS categoria, t.type_ca AS tipus, l.linkCreated, l.linkUpdated, tema.tema_ca AS tema, i.idioma_ca AS idioma
            FROM epgylzqu_elliotfern_intranet.db_links AS l
            INNER JOIN epgylzqu_elliotfern_intranet.aux_temes AS tema ON tema.id = l.cat
            INNER JOIN epgylzqu_elliotfern_intranet.aux_categories AS ca ON tema.idGenere = ca.id
            LEFT JOIN epgylzqu_elliotfern_intranet.db_links_type AS t ON t.id = l.tipus
            LEFT JOIN epgylzqu_elliotfern_intranet.aux_idiomes AS i ON l.lang = i.id
            ORDER BY l.nom ASC;";
    } elseif ($lang === "es") {
        $query = "SELECT l.id, l.nom, l.web, ca.categoria_es AS categoria, t.type_es AS tipus, l.linkCreated, l.linkUpdated, tema.tema_es AS tema, i.idioma_es AS idioma
            FROM epgylzqu_elliotfern_intranet.db_links AS l
            INNER JOIN epgylzqu_elliotfern_intranet.aux_temes AS tema ON tema.id = l.cat
            INNER JOIN epgylzqu_elliotfern_intranet.aux_categories AS ca ON tema.idGenere = ca.id
            LEFT JOIN epgylzqu_elliotfern_intranet.db_links_type AS t ON t.id = l.tipus
            LEFT JOIN epgylzqu_elliotfern_intranet.aux_idiomes AS i ON l.lang = i.id
            ORDER BY l.nom ASC;";
    } elseif ($lang === "en") {
        $query = "SELECT l.id, l.nom, l.web, ca.categoria_en AS categoria, t.type_en AS tipus, l.linkCreated, l.linkUpdated, tema.tema_en AS tema, i.idioma_en AS idioma
            FROM epgylzqu_elliotfern_intranet.db_links AS l
            INNER JOIN epgylzqu_elliotfern_intranet.aux_temes AS tema ON tema.id = l.cat
            INNER JOIN epgylzqu_elliotfern_intranet.aux_categories AS ca ON tema.idGenere = ca.id
            LEFT JOIN epgylzqu_elliotfern_intranet.db_links_type AS t ON t.id = l.tipus
            LEFT JOIN epgylzqu_elliotfern_intranet.aux_idiomes AS i ON l.lang = i.id
            ORDER BY l.nom ASC;";
    } elseif ($lang === "fr") {
        $query = "SELECT l.id, l.nom, l.web, ca.categoria_fr AS categoria, t.type_fr AS tipus, l.linkCreated, l.linkUpdated, tema.tema_fr AS tema, i.idioma_fr AS idioma
            FROM epgylzqu_elliotfern_intranet.db_links AS l
            INNER JOIN epgylzqu_elliotfern_intranet.aux_temes AS tema ON tema.id = l.cat
            INNER JOIN epgylzqu_elliotfern_intranet.aux_categories AS ca ON tema.idGenere = ca.id
            LEFT JOIN epgylzqu_elliotfern_intranet.db_links_type AS t ON t.id = l.tipus
            LEFT JOIN epgylzqu_elliotfern_intranet.aux_idiomes AS i ON l.lang = i.id
            ORDER BY l.nom ASC;";
    } elseif ($lang === "it") {
        $query = "SELECT l.id, l.nom, l.web, ca.categoria_it AS categoria, t.type_it AS tipus, l.linkCreated, l.linkUpdated, tema.tema_it AS tema, i.idioma_it AS idioma
            FROM epgylzqu_elliotfern_intranet.db_links AS l
            INNER JOIN epgylzqu_elliotfern_intranet.aux_temes AS tema ON tema.id = l.cat
            INNER JOIN epgylzqu_elliotfern_intranet.aux_categories AS ca ON tema.idGenere = ca.id
            LEFT JOIN epgylzqu_elliotfern_intranet.db_links_type AS t ON t.id = l.tipus
            LEFT JOIN epgylzqu_elliotfern_intranet.aux_idiomes AS i ON l.lang = i.id
            ORDER BY l.nom ASC;";
    }

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

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
} elseif (isset($_GET['type']) && $_GET['type'] == 'llistatArticlesCurs' && isset($_GET['lang']) && isset($_GET['id'])) {

    // Aquí puedes obtener los valores de los parámetros
    $lang = $_GET['lang'];
    $id = $_GET['id'];

    global $conn;

    if ($lang === "ca") {
        $query = "SELECT 
            courses.nameCa AS curso_titulo,
            posts.post_title AS articulo_titulo,
            posts.post_name AS articulo_url,
            courses.paramNameCa as curso_url
        FROM epgylzqu_elliotfern_intranet.db_openhistory_courses AS courses
        JOIN epgylzqu_historia_web.posts_lang AS articles ON courses.id = articles.curs
        JOIN epgylzqu_historia_web.xfr_posts AS posts ON articles.ca = posts.ID
        WHERE 
            articles.curs = (
                SELECT curs
                FROM epgylzqu_historia_web.posts_lang AS articles
                WHERE ca = :param
            )
        ORDER BY 
            articles.ordre";
    } elseif ($lang === "es") {
        $query = "SELECT 
        courses.nameEs AS curso_titulo,
        posts.post_title AS articulo_titulo,
        posts.post_name AS articulo_url,
        courses.paramNameEs as curso_url
    FROM epgylzqu_elliotfern_intranet.db_openhistory_courses AS courses
    JOIN epgylzqu_historia_web.posts_lang AS articles ON courses.id = articles.curs
    JOIN epgylzqu_historia_web.xfr_posts AS posts ON articles.es = posts.ID
    WHERE 
        articles.curs= (
            SELECT curs
            FROM epgylzqu_historia_web.posts_lang AS articles
            WHERE es = :param
        )
    ORDER BY 
        articles.ordre";
    } elseif ($lang === "en") {
        $query = "SELECT 
        courses.nameEn AS curso_titulo,
        posts.post_title AS articulo_titulo,
        posts.post_name AS articulo_url,
        courses.paramNameEn as curso_url
    FROM epgylzqu_elliotfern_intranet.db_openhistory_courses AS courses
    JOIN epgylzqu_historia_web.posts_lang AS articles ON courses.id = articles.curs
    JOIN epgylzqu_historia_web.xfr_posts AS posts ON articles.en = posts.ID
    WHERE 
        articles.curs = (
            SELECT curs
            FROM epgylzqu_historia_web.posts_lang AS articles 
            WHERE en = :param
        )
    ORDER BY 
        articles.ordre";
    } elseif ($lang === "fr") {
        $query = "SELECT 
        courses.nameFr AS curso_titulo,
        posts.post_title AS articulo_titulo,
        posts.post_name AS articulo_url,
        courses.paramNameFr as curso_url
    FROM epgylzqu_elliotfern_intranet.db_openhistory_courses AS courses
    JOIN epgylzqu_historia_web.posts_lang AS articles ON courses.id = articles.curs
    JOIN epgylzqu_historia_web.xfr_posts AS posts ON articles.fr = posts.ID
    WHERE 
        articles.curs = (
            SELECT curs
            FROM epgylzqu_historia_web.posts_lang AS articles
            WHERE fr = :param
        )
    ORDER BY 
        articles.ordre";
    } elseif ($lang === "it") {
        $query = "SELECT 
        courses.nameIt AS curso_titulo,
        posts.post_title AS articulo_titulo,
        posts.post_name AS articulo_url,
        courses.paramNameIt as curso_url
    FROM epgylzqu_elliotfern_intranet.db_openhistory_courses AS courses
    JOIN epgylzqu_historia_web.posts_lang AS articles ON courses.id = articles.curs
    JOIN epgylzqu_historia_web.xfr_posts AS posts ON articles.it = posts.ID
    WHERE 
        articles.curs = (
            SELECT curs
            FROM epgylzqu_historia_web.posts_lang AS articles
            WHERE it = :param
        )
    ORDER BY 
        articles.ordre";
    }

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Vincular los parámetros
    $stmt->bindParam(':param', $id);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
} elseif (isset($_GET['type']) && $_GET['type'] == 'articleIdiomes' && isset($_GET['lang']) && isset($_GET['id'])) {

    // Aquí puedes obtener los valores de los parámetros
    $lang = $_GET['lang'];
    $id = $_GET['id'];

    global $conn;

    if ($lang === "ca") {
        $query = "SELECT l.es, l.fr, l.en, l.it,
        pEs.post_title AS post_titleEs,
        pEs.post_name AS post_nameEs,
        pEn.post_title AS post_titleEn,
        pEn.post_name AS post_nameEn,
        pFr.post_title AS post_titleFr,
        pFr.post_name AS post_nameFr,
        pIt.post_title AS post_titleIt,
        pIt.post_name AS post_nameIt,
        pCa.post_title AS post_titleCa,
        pCa.post_name AS post_nameCa
        FROM epgylzqu_historia_web.posts_lang AS l
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pEs ON l.es = pEs.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pEn ON l.en = pEn.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pFr ON l.fr = pFr.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pIt ON l.it = pIt.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pCa ON l.ca = pCa.ID
        WHERE l.ca = :param";
    } elseif ($lang === "es") {
        $query = "SELECT l.es, l.fr, l.en, l.it,
        pEs.post_title AS post_titleEs,
        pEs.post_name AS post_nameEs,
        pEn.post_title AS post_titleEn,
        pEn.post_name AS post_nameEn,
        pFr.post_title AS post_titleFr,
        pFr.post_name AS post_nameFr,
        pIt.post_title AS post_titleIt,
        pIt.post_name AS post_nameIt,
        pCa.post_title AS post_titleCa,
        pCa.post_name AS post_nameCa
        FROM epgylzqu_historia_web.posts_lang AS l
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pEs ON l.es = pEs.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pEn ON l.en = pEn.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pFr ON l.fr = pFr.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pIt ON l.it = pIt.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pCa ON l.ca = pCa.ID
        WHERE l.es = :param";
    } elseif ($lang === "en") {
        $query = "SELECT l.es, l.fr, l.en, l.it,
        pEs.post_title AS post_titleEs,
        pEs.post_name AS post_nameEs,
        pEn.post_title AS post_titleEn,
        pEn.post_name AS post_nameEn,
        pFr.post_title AS post_titleFr,
        pFr.post_name AS post_nameFr,
        pIt.post_title AS post_titleIt,
        pIt.post_name AS post_nameIt,
        pCa.post_title AS post_titleCa,
        pCa.post_name AS post_nameCa
        FROM epgylzqu_historia_web.posts_lang AS l
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pEs ON l.es = pEs.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pEn ON l.en = pEn.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pFr ON l.fr = pFr.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pIt ON l.it = pIt.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pCa ON l.ca = pCa.ID
        WHERE l.en = :param";
    } elseif ($lang === "fr") {
        $query = "SELECT l.es, l.fr, l.en, l.it,
        pEs.post_title AS post_titleEs,
        pEs.post_name AS post_nameEs,
        pEn.post_title AS post_titleEn,
        pEn.post_name AS post_nameEn,
        pFr.post_title AS post_titleFr,
        pFr.post_name AS post_nameFr,
        pIt.post_title AS post_titleIt,
        pIt.post_name AS post_nameIt,
        pCa.post_title AS post_titleCa,
        pCa.post_name AS post_nameCa
        FROM epgylzqu_historia_web.posts_lang AS l
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pEs ON l.es = pEs.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pEn ON l.en = pEn.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pFr ON l.fr = pFr.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pIt ON l.it = pIt.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pCa ON l.ca = pCa.ID
        WHERE l.fr = :param";
    } elseif ($lang === "it") {
        $query = "SELECT l.es, l.fr, l.en, l.it,
        pEs.post_title AS post_titleEs,
        pEs.post_name AS post_nameEs,
        pEn.post_title AS post_titleEn,
        pEn.post_name AS post_nameEn,
        pFr.post_title AS post_titleFr,
        pFr.post_name AS post_nameFr,
        pIt.post_title AS post_titleIt,
        pIt.post_name AS post_nameIt,
        pCa.post_title AS post_titleCa,
        pCa.post_name AS post_nameCa
        FROM epgylzqu_historia_web.posts_lang AS l
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pEs ON l.es = pEs.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pEn ON l.en = pEn.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pFr ON l.fr = pFr.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pIt ON l.it = pIt.ID
        LEFT JOIN epgylzqu_historia_web.xfr_posts AS pCa ON l.ca = pCa.ID
        WHERE l.it = :param";
    }

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Vincular los parámetros
    $stmt->bindParam(':param', $id);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
} elseif (isset($_GET['type']) && $_GET['type'] == 'cursIdiomes' && isset($_GET['lang']) && isset($_GET['id'])) {

    // Aquí puedes obtener los valores de los parámetros
    $lang = $_GET['lang'];
    $id = $_GET['id'];

    global $conn;

    $query = "SELECT 
        c.paramNameEs AS post_nameEs,
        c.paramNameEn AS post_nameEn,
        c.paramNameFr AS post_nameFr,
        c.paramNameIt AS post_nameIt,
        c.paramNameCa AS post_nameCa
        FROM epgylzqu_elliotfern_intranet.db_openhistory_courses AS c
        WHERE c.id = :param";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Vincular los parámetros
    $stmt->bindParam(':param', $id);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
}
