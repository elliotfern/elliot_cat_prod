<?php

use App\Application\Client\Presenter\ClientResponse;
use App\Application\Client\Service\ClientService;
use App\Config\Database;
use App\Config\DatabaseConnection;
use App\Infrastructure\Persistence\Client\MysqlClientRepository;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Utils\AdminMiddleware;
use App\Utils\Uuid;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

$db = new Database();
$pdo = DatabaseConnection::getConnection();

$clientRepository = new MysqlClientRepository($db);
$clientService = new ClientService($clientRepository);

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

    AdminMiddleware::handle();
    $clients = $clientService->getAll();

    $data = array_map(
        fn($client) => ClientResponse::toArray($client),
        $clients
    );

    Response::success(
        message: MissatgesAPI::success('get'),
        data: $data,
        httpCode: 200
    );

    // GET : Detalls client ID
    // ruta => "https://elliot.cat/api/comptabilitat/get/clientId?id=i89jnbd"
} else if ($slug === 'clientId') {

    AdminMiddleware::handle();

    $id = $_GET['id'] ?? null;

    $client = $clientService->getById($id);

    if (!$client) {
        Response::error(
            message: MissatgesAPI::error('not_found'),
            httpCode: 404
        );
        return;
    }

    Response::success(
        message: MissatgesAPI::success('get'),
        data: ClientResponse::toArray($client),
        httpCode: 200
    );

    // GET : Pressupostos enviats a client ID
    // ruta => "https://elliot.cat/api/comptabilitat/get/pressupostosClientId?id=i89jnbd"
} else if ($slug === 'pressupostosClientId') {

    AdminMiddleware::handle();

    $id = $_GET['id'];

    $sql = <<<SQL
            SELECT 
            p.id, p.concepte, p.client_id, p.servei_id, p.estat_id, p.import, p.data, p.created_at, p.modified_at, c.id AS idClient, e.estat, s.producte, YEAR(p.data) AS any
            FROM %s AS p
            LEFT JOIN %s AS c ON p.client_id = c.id
            LEFT JOIN %s AS e ON p.estat_id = e.id
            LEFT JOIN %s AS s ON p.servei_id = s.id2
            WHERE c.id = :id
            ORDER BY p.data DESC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_PRESSUPOSTOS, $pdo),
        qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo),
        qi(Tables::DB_COMPTABILITAT_CLIENTS_ESTAT, $pdo),
        qi(Tables::DB_COMPTABILITAT_CATALEG_PRODUCTES, $pdo),
    );

    try {

        $params = [':id' => uuid::toBinary($id)];
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

    // GET : Pressupost ID
    // ruta => "https://elliot.cat/api/comptabilitat/get/pressupostId?id=i89jnbd"
} else if ($slug === 'pressupostId') {

    AdminMiddleware::handle();

    $id = $_GET['id'];
    $sql = <<<SQL
            SELECT 
            p.id, p.concepte, p.client_id, p.servei_id, p.estat_id, p.import, p.data, p.created_at, p.modified_at,
            c.id AS idClient, c.clientNom, c.clientCognoms, c.clientEmail, c.clientEmpresa, e.estat, s.producte, YEAR(p.data) AS any
            FROM %s AS p
            LEFT JOIN %s AS c ON p.client_id = c.id
            LEFT JOIN %s AS e ON p.estat_id = e.id
            LEFT JOIN %s AS s ON p.servei_id = s.id2
            WHERE p.id = :id
            LIMIT 1
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_PRESSUPOSTOS, $pdo),
        qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo),
        qi(Tables::DB_COMPTABILITAT_CLIENTS_ESTAT, $pdo),
        qi(Tables::DB_COMPTABILITAT_CATALEG_PRODUCTES, $pdo),
    );

    try {

        $params = [':id' => uuid::toBinary($id)];
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

    // GET : Factures enviades a client ID
    // ruta => "https://elliot.cat/api/comptabilitat/get/facturesClientId?id=i89jnbd"
} else if ($slug === 'facturesClientId') {

    AdminMiddleware::handle();

    $id = $_GET['id'];

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
        WHERE c.id = :id
        ORDER BY ic.data_factura DESC
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS, $pdo),
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_TIPUS_IVA, $pdo),
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_ESTAT, $pdo),
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_TIPUS_PAGAMENT, $pdo),
        qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo)
    );

    $sql2 = <<<SQL
        SELECT  
            SUM(ic.total_factura) AS total_facturat
            FROM %s ic
            WHERE ic.client_id = :id
    SQL;

    $query2 = sprintf(
        $sql2,
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS, $pdo)
    );

    try {
        $params = [':id' => uuid::toBinary($id)];
        $factures = $db->getData($query, $params, false);

        $totalRow = $db->getData($query2, $params, false);

        $total = $totalRow[0]['total_facturat'] ?? 0;

        Response::success(
            MissatgesAPI::success('get'),
            [
                'factures' => $factures ?? [],
                'totals' => [
                    'total_facturat' => (float)$total
                ]
            ],
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // GET : Llistat factures clients
    // ruta => "https://elliot.cat/api/comptabilitat/get/facturacioClients?id={id}"
} else if ($slug === 'facturacioClients') {

    AdminMiddleware::handle();

    $emissor_id = isset($_GET['id']) ? $_GET['id'] : null;

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
        $params = ['emissor_id' => uuid::toBinary($emissor_id)];
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

    // GET : Detall d'una factura amb productes
    // ruta => "https://elliot.cat/api/comptabilitat/get/facturaCompleta?id=1"
} else if ($slug === 'facturaCompleta') {

    AdminMiddleware::handle();

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
            LEFT JOIN %s AS pai ON pai.id = e.pais_id
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
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // GET : Llistat despeses
    // ruta => "https://elliot.cat/api/comptabilitat/get/despeses?receptor_id={id}&tipus_despesa={personal|professional}"
} else if ($slug === 'despeses') {

    AdminMiddleware::handle();

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

    // GET : Llistat d'emissors
    // ruta => "https://elliot.cat/api/comptabilitat/get/emissors"
} else if ($slug === 'emissors') {

    AdminMiddleware::handle();

    $sql = <<<SQL
        SELECT 
            e.id, 
            e.nom, 
            e.nif, 
            e.numero_iva, 
            e.pais_id, 
            p.pais_ca,
            e.adreca, 
            e.telefon, 
            e.email, 
            e.created_at, 
            e.updated_at
        FROM %s AS e
        LEFT JOIN %s AS p ON e.pais_id = p.id
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
    // GET : Obtenir emissor per ID
    // ruta => "https://elliot.cat/api/comptabilitat/get/emissorId?id={id}"
} else if ($slug === 'emissorId') {

    AdminMiddleware::handle();

    $emissor_id = $_GET['id'];

    $sql = <<<SQL
        SELECT e.id, e.nom, e.nif, e.numero_iva, p.pais_ca, e.adreca, e.telefon, e.email, e.pais_id
        FROM %s AS e
        LEFT JOIN %s AS p ON e.pais_id = p.id
        WHERE e.id = :emissor_id
        LIMIT 1
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_EMISSORS, $pdo),
        qi(Tables::DB_PAISOS, $pdo)
    );

    try {
        $params = [':emissor_id' => uuid::toBinary($emissor_id)];
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

    // GET : Llistat de productes
    // ruta => "https://elliot.cat/api/comptabilitat/get/productes"
} else if ($slug === 'productes') {

    AdminMiddleware::handle();

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

    // GET : Obtenir producte per ID
    // ruta => "https://elliot.cat/api/comptabilitat/get/producteId?id={id}"
} else if ($slug === 'producteId' && isset($_GET['id'])) {

    AdminMiddleware::handle();

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

    // GET : Llistat de proveïdors
    // ruta => "https://elliot.cat/api/comptabilitat/get/proveidors"
} else if ($slug === 'proveidors') {

    AdminMiddleware::handle();

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

    // GET : Detalls d'un proveidor per ID
    // ruta => https://elliot.cat/api/comptabilitat/get/proveidor?id={id}
} else if ($slug === 'proveidor') {

    AdminMiddleware::handle();

    $proveidor_id = $_GET['id'];

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
        $params = ['id' => uuid::toBinary($proveidor_id)];
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

    // GET : Detalls d'una factura de despesa per ID
    // ruta => https://elliot.cat/api/comptabilitat/get/despesa?id={id}
} else if ($slug === 'despesa') {

    AdminMiddleware::handle();

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

    // GET : Llistat Pressupostos
    // ruta => "https://elliot.cat/api/comptabilitat/get/pressupostos"
} else if ($slug === 'pressupostos') {

    AdminMiddleware::handle();

    $sql = <<<SQL
            SELECT 
            p.id, p.concepte, p.client_id, p.servei_id, p.estat_id, p.import, p.data, p.created_at, p.modified_at, c.id AS idClient, c.clientNom, c.clientCognoms, c.clientEmpresa, e.estat, s.producte, YEAR(p.data) AS any
            FROM %s AS p
            LEFT JOIN %s AS c ON p.client_id = c.id
            LEFT JOIN %s AS e ON p.estat_id = e.id
            LEFT JOIN %s AS s ON p.servei_id = s.id2
            ORDER BY p.data DESC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_PRESSUPOSTOS, $pdo),
        qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo),
        qi(Tables::DB_COMPTABILITAT_CLIENTS_ESTAT, $pdo),
        qi(Tables::DB_COMPTABILITAT_CATALEG_PRODUCTES, $pdo),
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
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    return;
}
