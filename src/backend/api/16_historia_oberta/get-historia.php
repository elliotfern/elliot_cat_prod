<?php


use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;

$slug = $routeParams[0] ?? '';
$db = new Database();
$pdo = $db->getPdo();

header("Content-Type: application/json; charset=utf-8");
corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// 2. Llistat de càrrecs d'una persona
// ruta GET => "/api/historia/get/carrecsPersona?id=234"
if ($slug === 'carrecsPersona') {
    $id = $_GET['id'];

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
    // ruta GET => "/api/historia/get/esdevenimentsPersona?id=234"
} else if ($slug === 'esdevenimentsPersona') {
    $id = $_GET['id'];

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
    // ruta GET => "/api/historia/get/llistatEsdeveniments"
} else if ($slug === 'llistatEsdeveniments') {
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
    // ruta GET => "/api/historia/get/subEtapesEtapa?id=1"
} else if ($slug === 'subEtapesEtapa') {
    $id = $_GET['id'];

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
    // ruta GET => "/api/historia/get/llistatSubEtapes"
} else if ($slug === 'llistatSubEtapes') {

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
    // ruta GET => "/api/historia/get/esdeveniment?slug=revolucio-vellut"
} else if ($slug === 'esdeveniment') {
    $slug = $_GET['slug'];

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
    // ruta GET => "/api/historia/get/llistatImatgesEsdeveniments"
} else if ($slug === 'llistatImatgesEsdeveniments') {

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
    // ruta GET => "/api/historia/get/personesEsdeveniments?id=234"
} else if ($slug === 'personesEsdeveniments') {
    $id = $_GET['id'];

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
    // ruta GET => "/api/historia/get/organitzacionsEsdeveniments?id=234"
} else if ($slug === 'organitzacionsEsdeveniments') {
    $id = $_GET['id'];

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
    // ruta GET => "/api/historia/get/llistatEsdevenimentsSelect"
} else if ($slug === 'llistatEsdevenimentsSelect') {

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
    // ruta GET => "/api/historia/get/llistatPersones"
} else if ($slug === 'llistatPersones') {

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
    // ruta GET => "/api/historia/get/formEsdevenimentsPersona?id=234"
} else if ($slug === 'formEsdevenimentsPersona') {
    $id = $_GET['id'];

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
    // ruta GET => "/api/historia/get/llistatOrganitzacions"
} else if ($slug === 'llistatOrganitzacions') {

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
    // ruta GET => "/api/historia/get/formEsdevenimentOrganitzacions?id=22"
} else if ($slug === 'formEsdevenimentOrganitzacions') {
    $id = $_GET['id'];

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
    // ruta GET => "/api/historia/get/personaCarrec?id="33"
} else if ($slug === 'personaCarrec') {
    $id = $_GET['id'];

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
    // ruta GET => "/api/historia/get/paginaOrganitzacions"
} else if ($slug === 'paginaOrganitzacions') {

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
    // ruta GET => "/api/historia/get/fitxaOrganitzacio?slug=primera-internacional"
} else if ($slug === 'fitxaOrganitzacio') {
    $slug = $_GET['slug'];

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
    // ruta GET => "/api/historia/get/esdevenimentsOrganitzacio?id=22"
} else if ($slug === 'esdevenimentsOrganitzacio') {
    $id = $_GET['id'];

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
    // ruta GET => "/api/historia/get/carrecsPersonesOrganitzacio?id=22"
} else if ($slug === 'carrecsPersonesOrganitzacio') {
    $id = $_GET['id'];

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
    // ruta GET => "/api/historia/get/llistatImatgesOrganitzacions"
} else if ($slug === 'llistatImatgesOrganitzacions') {

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
    // ruta GET => "/api/historia/get/llistatIdeologies"
} else if ($slug === 'llistatIdeologies') {

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
    // ruta GET => "/api/historia/get/llistatTipusOrganitzacio"
} else if ($slug === 'llistatTipusOrganitzacio') {

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

    // Llistat cursos
    // ruta GET => "/api/historia/get/llistatCursos"
} else if ($slug === 'llistatCursos') {
    global $conn;
    $lang = $_GET['langCurso'];

    if ($lang === "ca") {
        $sql = "SELECT id,
        ordre,
        nameCa AS nombreCurso,
        resumenCa AS resumen,
        img,
        paramNameCa AS paramName,
        lastModified
        FROM db_historia_oberta_cursos 
        ORDER BY ordre ASC";
    } else if ($lang === "en") {
        $sql = "SELECT id,
        ordre,
        nameEn AS nombreCurso,
        resumenEn AS resumen,
        img,
        paramNameEn AS paramName,
        lastModified
        FROM db_historia_oberta_cursos 
        ORDER BY ordre ASC";
    } else if ($lang === "es") {
        $sql = "SELECT id,
        ordre,
        nameEs AS nombreCurso,
        resumenEs AS resumen,
        img,
        paramNameEs AS paramName,
        lastModified
        FROM db_historia_oberta_cursos 
        ORDER BY ordre ASC";
    } else if ($lang === "fr") {
        $sql = "SELECT id,
        ordre,
        nameFr AS nombreCurso,
        resumenFr AS resumen,
        img,
        paramNameFr AS paramName,
        lastModified
        FROM db_historia_oberta_cursos 
        ORDER BY ordre ASC";
    } else if ($lang === "it") {
        $sql = "SELECT id,
        ordre,
        nameIt AS nombreCurso,
        resumenIt AS resumen,
        img,
        paramNameIt AS paramName,
        lastModified
        FROM db_historia_oberta_cursos 
        ORDER BY ordre ASC";
    }

    try {
        $result = $db->getData($sql);

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
} else if ($slug === 'cursHistoria') {
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

    // Llistat arxiu articles
} else if ($slug === 'arxiuArticles') {

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



    /**
     * GET : Slots/articles d'un curs (amb títols per idioma)
     * URL: /api/historia-oberta/get/cursArticles?cursId=3
     */
} else if ($slug === 'cursArticles') {

    $cursId = isset($_GET['cursId']) ? (int)$_GET['cursId'] : 0;
    if ($cursId <= 0) {
        Response::error(MissatgesAPI::error('invalid_param'), ['cursId'], 400);
        return;
    }

    // 1) Curs info (opcional, però útil)
    $sqlCurs = sprintf(
        "SELECT id, ordre, nameCa, nameEs, nameEn, nameIt, nameFr, img, lastModified
         FROM %s
         WHERE id = :id
         LIMIT 1",
        qi(Tables::DB_HISTORIA_OBERTA_CURSOS, $pdo)
    );

    // 2) Slots + joins a db_blog (títols)
    $sql = sprintf(
        "SELECT
            a.id AS slotId,
            a.curs,
            a.ordre,

            a.ca AS ca_id,
            bca.post_title AS ca_title,
            bca.slug AS ca_slug,
            bca.post_status AS ca_status,

            a.es AS es_id,
            bes.post_title AS es_title,
            bes.slug AS es_slug,
            bes.post_status AS es_status,

            a.en AS en_id,
            ben.post_title AS en_title,
            ben.slug AS en_slug,
            ben.post_status AS en_status,

            a.fr AS fr_id,
            bfr.post_title AS fr_title,
            bfr.slug AS fr_slug,
            bfr.post_status AS fr_status,

            a.it AS it_id,
            bit.post_title AS it_title,
            bit.slug AS it_slug,
            bit.post_status AS it_status

        FROM %s AS a
        LEFT JOIN %s AS bca ON bca.id = a.ca
        LEFT JOIN %s AS bes ON bes.id = a.es
        LEFT JOIN %s AS ben ON ben.id = a.en
        LEFT JOIN %s AS bfr ON bfr.id = a.fr
        LEFT JOIN %s AS bit ON bit.id = a.it

        WHERE a.curs = :cursId
        ORDER BY a.ordre ASC, a.id ASC",
        qi(Tables::DB_HISTORIA_OBERTA_ARTICLES, $pdo),
        qi(Tables::BLOG, $pdo),
        qi(Tables::BLOG, $pdo),
        qi(Tables::BLOG, $pdo),
        qi(Tables::BLOG, $pdo),
        qi(Tables::BLOG, $pdo)
    );

    try {
        $curs = $db->getData($sqlCurs, [':id' => $cursId], true);

        if (empty($curs)) {
            Response::error(MissatgesAPI::error('not_found'), ['curs not found'], 404);
            return;
        }

        $items = $db->getData($sql, [':cursId' => $cursId], false) ?? [];

        // normalitza una mica per frontend
        $normalized = array_map(function ($r) {
            $mk = function ($prefix) use ($r) {
                $id = $r[$prefix . '_id'] ?? null;
                if ($id === null) return null;
                return [
                    'id' => (int)$id,
                    'title' => (string)($r[$prefix . '_title'] ?? ''),
                    'slug' => (string)($r[$prefix . '_slug'] ?? ''),
                    'status' => (string)($r[$prefix . '_status'] ?? ''),
                    'editUrl' => "/gestio/blog/modifica-article/" . (int)$id
                ];
            };

            return [
                'slotId' => (int)$r['slotId'],
                'curs' => (int)$r['curs'],
                'ordre' => (int)$r['ordre'],
                'ca' => $mk('ca'),
                'es' => $mk('es'),
                'en' => $mk('en'),
                'fr' => $mk('fr'),
                'it' => $mk('it'),
            ];
        }, $items);

        Response::success(
            MissatgesAPI::success('get'),
            [
                'curs' => $curs,
                'items' => $normalized
            ],
            200
        );
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    return;
}
