<?php

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

$db = new Database();
$pdo = $db->getPdo();

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);


// Verificar que el método de la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}


// GET : Llistat clients
// ruta => "https://elliot.cat/api/comptabilitat/get/clients"
if ($slug === 'clients') {

    if (!isAuthenticatedAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'No autoritzat (admin requerit)']);
        exit;
    }

    $sql = <<<SQL
            SELECT c.id, c.clientNom, c.clientCognoms, c.clientEmail, c.clientWeb, c.clientNIF, c.clientEmpresa, c.clientAdreca, c.clientCP, c.ciutat_id, c.provincia_id, c.pais_id, c.clientTelefon, c.clientRegistre, ci.ciutat_ca, co.pais_ca, cou.provincia_ca, c.clientStatus, s.estatNom
            FROM %s AS c
            LEFT JOIN %s AS ci ON c.ciutat_id = ci.id
            LEFT JOIN %s AS co ON c.pais_id = co.id
            LEFT JOIN %s AS cou ON c.provincia_id = cou.id
            LEFT JOIN %s AS s ON c.clientStatus = s.id
            ORDER BY c.clientCognoms ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo),
        qi(Tables::DB_CIUTATS, $pdo),
        qi(Tables::DB_PAISOS, $pdo),
        qi(Tables::DB_PROVINCIES, $pdo),
        qi(Tables::DB_COMPTABILITAT_CLIENTS_ESTAT, $pdo)
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

    // GET : Detalls client ID
    // ruta => "https://elliot.cat/api/comptabilitat/get/clientId"
} else if ($slug === 'clientId') {


    if (!isAuthenticatedAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'No autoritzat (admin requerit)']);
        exit;
    }

    $id = $_GET['id'];
    $sql = <<<SQL
            SELECT c.id, c.clientNom, c.clientCognoms, c.clientEmail, c.clientWeb, c.clientNIF, c.clientEmpresa, c.clientAdreca, c.clientCP, c.ciutat_id, c.provincia_id, c.pais_id, c.clientTelefon, c.clientRegistre, ci.ciutat_ca, co.pais_ca, cou.provincia_ca, c.clientStatus, s.estatNom
            FROM %s AS c
            LEFT JOIN %s AS ci ON c.ciutat_id = ci.id
            LEFT JOIN %s AS co ON c.pais_id = co.id
            LEFT JOIN %s AS cou ON c.provincia_id = cou.id
            LEFT JOIN %s AS s ON c.clientStatus = s.id
            WHERE c.id = :id
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo),
        qi(Tables::DB_CIUTATS, $pdo),
        qi(Tables::DB_PAISOS, $pdo),
        qi(Tables::DB_PROVINCIES, $pdo),
        qi(Tables::DB_COMPTABILITAT_CLIENTS_ESTAT, $pdo)
    );

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

    // GET : Llistat factures clients
    // ruta => "https://elliot.cat/api/comptabilitat/get/facturacioClients?emissor_id={id}"
} else if ($slug === 'facturacioClients') {


    if (!isAuthenticatedAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'No autoritzat (admin requerit)']);
        exit;
    }

    $emissor_id = isset($_GET['emissor_id']) ? (int) $_GET['emissor_id'] : null;

    $sql = <<<SQL
        SELECT 
            ic.id,
            ic.numero_factura,
            ic.emissor_id,
            ic.client_id,
            ic.concepte,
            ic.data_factura,
            YEAR(ic.data_factura) AS yearInvoice,
            CONCAT('Any ', YEAR(ic.data_factura)) AS any,
            ic.data_venciment,
            ic.base_imposable,
            ic.despeses_extra,
            ic.total_factura,
            ic.import_iva,
            ic.tipus_iva,
            ic.estat,
            ic.metode_pagament,
            vt.ivaPercen,
            ist.estat,
            pt.tipus AS tipusNom,
            pt.notes,
            c.clientNom,
            c.clientCognoms,
            c.clientEmpresa
        FROM %s AS ic
        LEFT JOIN %s AS vt ON ic.tipus_iva = vt.id
        LEFT JOIN %s AS ist ON ist.id = ic.estat
        LEFT JOIN %s AS pt ON ic.metode_pagament = pt.id
        LEFT JOIN %s AS c ON ic.client_id = c.id
        WHERE ic.emissor_id = :emissor_id
        ORDER BY ic.id DESC
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS, $pdo),
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_TIPUS_IVA, $pdo),
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_ESTAT, $pdo),
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_TIPUS_PAGAMENT, $pdo),
        qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo)
    );

    try {

        $params = [
            'emissor_id' => $emissor_id
        ];

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

    // GET : Detall d'una factura amb productes
    // ruta => "https://elliot.cat/api/comptabilitat/get/facturaCompleta?id=1"
} else if ($slug === 'facturaCompleta') {


    if (!isAuthenticatedAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'No autoritzat (admin requerit)']);
        exit;
    }

    $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
    $pdf = isset($_GET['pdf']) ? true : false; // Parámetro opcional

    if (!$id) {
        Response::error(MissatgesAPI::error('missing_id'), [], 400);
        return;
    }

    try {
        // 1️⃣ Factura principal + datos cliente + IVA + estado + método pago
        $sqlFactura = <<<SQL
            SELECT 
                ic.id,
                ic.client_id,
                ic.emissor_id,
                ic.concepte,
                ic.data_factura,
                ic.numero_factura,
                YEAR(ic.data_factura) AS yearInvoice,
                CONCAT('Any ', YEAR(ic.data_factura)) AS any,
                ic.data_venciment,
                ic.base_imposable,
                ic.despeses_extra,
                ic.total_factura,
                ic.import_iva,
                ic.tipus_iva,
                ic.estat,
                ic.metode_pagament,
                ic.notes,
                ic.projecte_id,
                ic.arxiu_url,
                ic.recurrent,
                ic.frequencia,
                vt.ivaPercen,
                ist.estat AS estatNom,
                pt.tipus AS tipusNom,
                pt.notes AS metodeNotes,
                c.clientNom,
                c.clientCognoms,
                c.clientEmpresa,
                c.clientEmail,
                c.clientWeb,
                c.clientNIF,
                c.clientAdreca,
                ciu.ciutat AS clientCiutat,
                pro.provincia_ca AS clientProvincia,
                pa.pais_ca AS clientPais,
                c.clientCP,
                e.nom AS emissorNom,
                e.nif AS emissorNIF,
                e.numero_iva AS emissorNumeroIVA,
                e.adreca AS emissorAdreca,
                e.telefon AS emissorTelefon,
                e.email AS emissorEmail,
                pai.pais_ca AS emissorPais
            FROM %s AS ic
            LEFT JOIN %s AS vt ON ic.tipus_iva = vt.id
            LEFT JOIN %s AS ist ON ist.id = ic.estat
            LEFT JOIN %s AS pt ON ic.metode_pagament = pt.id
            LEFT JOIN %s AS c ON ic.client_id = c.id
            LEFT JOIN %s AS ciu ON ciu.id = c.ciutat_id
            LEFT JOIN %s AS pro ON pro.id = c.provincia_id
            LEFT JOIN %s AS pa ON pa.id = c.pais_id
            LEFT JOIN %s AS e ON e.id = ic.emissor_id
            LEFT JOIN %s AS pai ON pai.id = e.pais
            WHERE ic.id = :id
            LIMIT 1
        SQL;

        $queryFactura = sprintf(
            $sqlFactura,
            qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS, $pdo),
            qi(Tables::DB_COMPTABILITAT_FACTURACIO_TIPUS_IVA, $pdo),
            qi(Tables::DB_COMPTABILITAT_FACTURACIO_ESTAT, $pdo),
            qi(Tables::DB_COMPTABILITAT_FACTURACIO_TIPUS_PAGAMENT, $pdo),
            qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo),
            qi(Tables::DB_CIUTATS, $pdo),
            qi(Tables::DB_PROVINCIES, $pdo),
            qi(Tables::DB_PAISOS, $pdo),
            qi(Tables::DB_COMPTABILITAT_EMISSORS, $pdo),
            qi(Tables::DB_PAISOS, $pdo),
        );

        $result = $db->getData($queryFactura, [':id' => $id], true);

        if (!$result) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        // 2️⃣ Productos asociados por numero_factura
        $sqlProductes = <<<SQL
            SELECT 
                p.id,
                p.factura_id,
                pd.producte,
                p.producte_id,
                p.descripcio,
                p.preu
            FROM %s AS p
            LEFT JOIN %s AS pd ON pd.id = p.producte_id
            WHERE p.factura_id = :numero_factura
            ORDER BY p.id ASC
        SQL;

        $queryProductes = sprintf(
            $sqlProductes,
            qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS_PRODUCTES, $pdo),
            qi(Tables::DB_COMPTABILITAT_CATALEG_PRODUCTES, $pdo)
        );

        $productes = $db->getData($queryProductes, [':numero_factura' => $result['numero_factura']], false);

        // 3️⃣ Devolvemos todo junto
        Response::success(
            MissatgesAPI::success('get'),
            [
                'factura' => $result,
                'productes' => $productes
            ],
            200
        );
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : Llistat despeses
    // ruta => "https://elliot.cat/api/comptabilitat/get/despeses?receptor_id={id}&tipus_despesa={personal|professional}"
} else if ($slug === 'despeses') {


    if (!isAuthenticatedAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'No autoritzat (admin requerit)']);
        exit;
    }

    $receptor_id = isset($_GET['receptor_id']) ? (int) $_GET['receptor_id'] : null;
    $tipus_despesa = isset($_GET['tipus_despesa']) ? $_GET['tipus_despesa'] : null;

    $sql = <<<SQL
        SELECT 
            d.id,
            d.data,
            YEAR(d.data) AS yearDespesa,
            CONCAT('Any ', YEAR(d.data)) AS any,
            d.data_pagament,
            d.concepte,
            d.receptor_id,
            d.proveidor_id,
            d.base_imposable,
            d.tipus_iva,
            d.import_iva,
            d.total,
            d.metode_pagament,
            d.pagat,
            d.tipus_despesa,
            d.categoria_id,
            d.subcategoria_id,
            d.deduible,
            d.recurrent,
            d.frequencia,
            d.notes,
            p.nom AS proveidorNom,
            p.id AS proveidorId,
            c.nom AS nomCategoria,
            s.nom AS nomSubCategoria
        FROM %s AS d
        LEFT JOIN %s AS p ON d.proveidor_id = p.id
        LEFT JOIN %s AS c ON d.categoria_id = c.id
        LEFT JOIN %s AS s ON d.subcategoria_id = s.id
        WHERE d.tipus_despesa = :tipus_despesa
        AND d.receptor_id = :receptor_id
        ORDER BY d.data DESC
SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_DESPESES, $pdo),
        qi(Tables::DB_COMPTABILITAT_PROVEIDORS, $pdo),
        qi(Tables::DB_COMPTABILITAT_CATEGORIES_DESPESA, $pdo),
        qi(Tables::DB_COMPTABILITAT_SUBCATEGORIES_DESPESA, $pdo)
    );

    try {

        $params = [
            'tipus_despesa' => $tipus_despesa,
            'receptor_id' => $receptor_id
        ];

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

    // GET : Llistat d'emissors
    // ruta => "https://elliot.cat/api/comptabilitat/get/emissors"
} else if ($slug === 'emissors') {


    if (!isAuthenticatedAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'No autoritzat (admin requerit)']);
        exit;
    }

    $sql = <<<SQL
        SELECT 
            e.id, 
            e.nom, 
            e.nif, 
            e.numero_iva, 
            e.pais as pais_id, 
            p.pais_ca,
            e.adreca, 
            e.telefon, 
            e.email, 
            e.created_at, 
            e.updated_at
        FROM %s AS e
        LEFT JOIN %s AS p ON e.pais = p.id
        ORDER BY e.nom ASC
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_EMISSORS, $pdo),
        qi(Tables::DB_PAISOS, $pdo)
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
    // GET : Obtenir emissor per ID
    // ruta => "https://elliot.cat/api/comptabilitat/get/emissorId?id={id}"
} else if ($slug === 'emissorId' && isset($_GET['id'])) {

    if (!isAuthenticatedAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'No autoritzat (admin requerit)']);
        exit;
    }

    $emissor_id = (int) $_GET['id'];

    $sql = <<<SQL
        SELECT e.id, e.nom, e.nif, e.numero_iva, e.pais, p.pais_ca, e.adreca, e.telefon, e.email
        FROM %s AS e
        LEFT JOIN %s AS p ON e.pais = p.id
        WHERE e.id = :emissor_id
        LIMIT 1
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_EMISSORS, $pdo),
        qi(Tables::DB_PAISOS, $pdo)
    );

    try {
        $params = [':emissor_id' => $emissor_id];
        $result = $db->getData($query, $params, true);

        if (!$result) {
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

    // GET : Llistat de productes
    // ruta => "https://elliot.cat/api/comptabilitat/get/productes"
} else if ($slug === 'productes') {


    if (!isAuthenticatedAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'No autoritzat (admin requerit)']);
        exit;
    }

    $sql = <<<SQL
        SELECT 
            p.id,
            p.producte,
            p.descripcio,
            p.unitat,
            p.preu_recomanat,
            p.actiu,
            p.created_at,
            p.updated_at
        FROM %s AS p
        ORDER BY p.producte ASC
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_CATALEG_PRODUCTES, $pdo)
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

    // GET : Obtenir producte per ID
    // ruta => "https://elliot.cat/api/comptabilitat/get/producteId?id={id}"
} else if ($slug === 'producteId' && isset($_GET['id'])) {


    if (!isAuthenticatedAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'No autoritzat (admin requerit)']);
        exit;
    }

    $producte_id = (int) $_GET['id'];

    $sql = <<<SQL
        SELECT id, producte, descripcio, actiu, unitat, preu_recomanat
        FROM %s
        WHERE id = :producte_id
        LIMIT 1
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_CATALEG_PRODUCTES, $pdo)
    );

    try {

        $params = [':producte_id' => $producte_id];
        $result = $db->getData($query, $params, true);

        if (!$result) {
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

    // GET : Llistat de proveïdors
    // ruta => "https://elliot.cat/api/comptabilitat/get/proveidors"
} else if ($slug === 'proveidors') {

    if (!isAuthenticatedAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'No autoritzat (admin requerit)']);
        exit;
    }

    $sql = <<<SQL
        SELECT 
            id,
            nom,
            nif,
            adreca,
            ciutat,
            codi_postal,
            pais,
            telefon,
            email,
            web,
            contacte,
            notes,
            created_at,
            updated_at
        FROM %s
        ORDER BY nom ASC
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_PROVEIDORS, $pdo)
    );

    try {
        $result = $db->getData($query, []);

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

    // GET : Detalls d'un proveidor per ID
    // ruta => https://elliot.cat/api/comptabilitat/get/proveidor?id={id}
} else if ($slug === 'proveidor') {

    if (!isAuthenticatedAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'No autoritzat (admin requerit)']);
        exit;
    }

    $proveidor_id = isset($_GET['id']) ? (int) $_GET['id'] : null;

    if (!$proveidor_id) {
        Response::error(
            MissatgesAPI::error('missing_id'),
            [],
            400
        );
        return;
    }

    $sql = <<<SQL
        SELECT 
            id,
            nom,
            nif,
            adreca,
            ciutat,
            codi_postal,
            pais,
            telefon,
            email,
            web,
            contacte,
            notes,
            created_at,
            updated_at
        FROM %s
        WHERE id = :id
        LIMIT 1
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_PROVEIDORS, $pdo)
    );

    try {
        $params = ['id' => $proveidor_id];
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
            $result[0],
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : Detalls d'una factura de despesa per ID
    // ruta => https://elliot.cat/api/comptabilitat/get/despesa?id={id}
} else if ($slug === 'despesa') {

    if (!isAuthenticatedAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'No autoritzat (admin requerit)']);
        exit;
    }

    $despesa_id = isset($_GET['id']) ? (int) $_GET['id'] : null;

    if (!$despesa_id) {
        Response::error(
            MissatgesAPI::error('missing_id'),
            [],
            400
        );
        return;
    }

    $sql = <<<SQL
        SELECT 
            id,
            data,
            data_pagament,
            concepte,
            proveidor_id,
            receptor_id,
            base_imposable,
            tipus_iva,
            import_iva,
            total,
            metode_pagament,
            pagat,
            categoria_id,
            subcategoria_id,
            tipus_despesa,
            client_id,
            projecte_id,
            arxiu_url,
            deduible,
            recurrent,
            frequencia,
            notes,
            created_at,
            updated_at
        FROM %s
        WHERE id = :id
        LIMIT 1
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_DESPESES, $pdo)
    );

    try {
        $params = ['id' => $despesa_id];
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
            $result[0],
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
