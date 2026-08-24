<?php

use App\Application\Client\Service\ClientService;
use App\Config\Database;
use App\Config\DatabaseConnection;
use App\Infrastructure\Persistence\Client\MysqlClientRepository;
use App\Infrastructure\Security\Auth\AuthFactory;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
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
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    http_response_code(204);
    exit;
}


// Verificar que el método de la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}


// GET : Llistat clients
// ruta => "api/comptabilitat/get/clients"
if ($slug === 'clients') {

    AuthFactory::admin()->handle();

    $sql = <<<SQL
            SELECT
                c.id,
                c.tipus_persona,
                c.nom,
                c.cognoms,
                c.empresa,
                c.email,
                c.web,
                c.nif,
                c.tel_1,
                c.tel_2,
                c.adreca,
                c.cp,
                c.ciutat_id,
                c.provincia_id,
                c.pais_id,
                c.data_naixement,

                cc.id AS client_id,
                cc.estat_id,
                cc.created_at,

                e.num,
                e.estat,

                p.provincia_ca,
                pa.pais_ca,
                ci.ciutat_ca

            FROM %s AS cc

            INNER JOIN %s AS c
                ON cc.contacte_id = c.id

            INNER JOIN %s AS e
                ON cc.estat_id = e.id

            LEFT JOIN %s AS p
                ON c.provincia_id = p.id

            LEFT JOIN %s AS pa
                ON c.pais_id = pa.id

            LEFT JOIN %s AS ci
                ON c.ciutat_id = ci.id

            ORDER BY
                c.cognoms ASC,
                c.nom ASC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo),
        qi(Tables::DB_CONTACTES, $pdo),
        qi(Tables::DB_COMPTABILITAT_CLIENTS_ESTAT, $pdo),
        qi(Tables::DB_PROVINCIES, $pdo),
        qi(Tables::DB_PAISOS, $pdo),
        qi(Tables::DB_CIUTATS, $pdo)
    );

    try {

        $result = $db->getData($query, [], false);

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

    // GET : Detalls client ID
    // ruta => "https://elliot.cat/api/comptabilitat/get/clientId?id=i89jnbd"
} else if ($slug === 'clientId') {

    AuthFactory::admin()->handle();
    $id = $_GET['id'] ?? null;

    $id = $_GET['id'] ?? null;

    $sql = <<<SQL
        SELECT
            c.id,
            c.nom,
            c.cognoms,
            c.email,
            c.web,
            c.nif,
            c.empresa,
            c.adreca,
            c.cp,
            c.ciutat_id,
            c.provincia_id,
            c.pais_id,
            c.tel_1,
            c.tel_2,
            
            cl.id AS client_id,
            cl.contacte_id,
            cl.estat_id,

            e.num,
            e.estat,

            p.provincia_ca,
            pa.pais_ca,
            ci.ciutat_ca

        FROM %s AS cl

        INNER JOIN %s AS c
            ON cl.contacte_id = c.id

        INNER JOIN %s AS e
            ON cl.estat_id = e.id

        LEFT JOIN %s AS p
            ON c.provincia_id = p.id

        LEFT JOIN %s AS pa
            ON c.pais_id = pa.id

        LEFT JOIN %s AS ci
            ON c.ciutat_id = ci.id

        WHERE c.id = :id

        LIMIT 1
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo),
        qi(Tables::DB_CONTACTES, $pdo),
        qi(Tables::DB_COMPTABILITAT_CLIENTS_ESTAT, $pdo),
        qi(Tables::DB_PROVINCIES, $pdo),
        qi(Tables::DB_PAISOS, $pdo),
        qi(Tables::DB_CIUTATS, $pdo),
    );

    try {

        $params = [':id' => Uuid::toBinary($id)];
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

    // GET : Pressupostos enviats a client ID
    // ruta => "https://elliot.cat/api/comptabilitat/get/pressupostosClientId?id=i89jnbd"
} else if ($slug === 'pressupostosClientId') {

    AuthFactory::admin()->handle();

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

    AuthFactory::admin()->handle();

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

    AuthFactory::admin()->handle();

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

    AuthFactory::admin()->handle();

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

            c.nom,
            c.cognoms,
            c.empresa

        FROM %s AS ic

        LEFT JOIN %s AS vt
            ON ic.tipus_iva = vt.id

        LEFT JOIN %s AS ist
            ON ist.id = ic.estat

        LEFT JOIN %s AS pt
            ON ic.metode_pagament = pt.id

        LEFT JOIN %s AS cl
            ON ic.client_id = cl.id

        LEFT JOIN %s AS c
            ON cl.contacte_id = c.id

        WHERE ic.emissor_id = :emissor_id

        ORDER BY ic.id DESC
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS, $pdo),
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_TIPUS_IVA, $pdo),
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_ESTAT, $pdo),
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_TIPUS_PAGAMENT, $pdo),
        qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo),
        qi(Tables::DB_CONTACTES, $pdo)
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

    $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

    if (!$id) {
        Response::error(
            MissatgesAPI::error('missing_id'),
            [],
            400
        );
        return;
    }

    try {

        // ---------------------------------------------------------
        // 1. FACTURA + CLIENT + CONTACTE + EMISSOR
        // ---------------------------------------------------------

        $sqlFactura = <<<SQL
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

                ic.notes,
                ic.projecte_id,
                ic.arxiu_url,
                ic.recurrent,
                ic.frequencia,

                vt.ivaPercen,

                ist.estat AS estatNom,

                pt.tipus AS tipusNom,
                pt.notes AS metodeNotes,

                -- CONTACTE DEL CLIENT
                co.nom AS clientNom,
                co.cognoms AS clientCognoms,
                co.empresa AS clientEmpresa,
                co.email AS clientEmail,
                co.web AS clientWeb,
                co.nif AS clientNIF,
                co.adreca AS clientAdreca,
                co.cp AS clientCP,

                ciu.ciutat_ca AS clientCiutat,
                pro.provincia_ca AS clientProvincia,
                pa.pais_ca AS clientPais,

                -- EMISSOR
                e.nom AS emissorNom,
                e.nif AS emissorNIF,
                e.numero_iva AS emissorNumeroIVA,
                e.adreca AS emissorAdreca,
                e.telefon AS emissorTelefon,
                e.email AS emissorEmail,

                pai.pais_ca AS emissorPais

            FROM %s AS ic

            LEFT JOIN %s AS vt
                ON ic.tipus_iva = vt.id

            LEFT JOIN %s AS ist
                ON ic.estat = ist.id

            LEFT JOIN %s AS pt
                ON ic.metode_pagament = pt.id

            -- CLIENT
            LEFT JOIN %s AS c
                ON ic.client_id = c.id

            -- CONTACTE
            LEFT JOIN %s AS co
                ON c.contacte_id = co.id

            -- CIUTAT DEL CONTACTE
            LEFT JOIN %s AS ciu
                ON co.ciutat_id = ciu.id

            -- PROVÍNCIA DEL CONTACTE
            LEFT JOIN %s AS pro
                ON co.provincia_id = pro.id

            -- PAÍS DEL CONTACTE
            LEFT JOIN %s AS pa
                ON co.pais_id = pa.id

            -- EMISSOR
            LEFT JOIN %s AS e
                ON ic.emissor_id = e.id

            -- PAÍS DE L'EMISSOR
            LEFT JOIN %s AS pai
                ON e.pais_id = pai.id

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
            qi(Tables::DB_CONTACTES, $pdo),
            qi(Tables::DB_CIUTATS, $pdo),
            qi(Tables::DB_PROVINCIES, $pdo),
            qi(Tables::DB_PAISOS, $pdo),
            qi(Tables::DB_COMPTABILITAT_EMISSORS, $pdo),
            qi(Tables::DB_PAISOS, $pdo)
        );

        $result = $db->getData(
            $queryFactura,
            [':id' => $id],
            true
        );

        if (!$result) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }


        // ---------------------------------------------------------
        // 2. PRODUCTES DE LA FACTURA
        // ---------------------------------------------------------

        $sqlProductes = <<<SQL
            SELECT
                p.id,
                p.factura_id,
                p.producte_id,
                pd.producte,
                p.descripcio,
                p.preu

            FROM %s AS p

            LEFT JOIN %s AS pd
                ON pd.id = p.producte_id

            WHERE p.factura_id = :factura_id

            ORDER BY p.id ASC
        SQL;

        $queryProductes = sprintf(
            $sqlProductes,
            qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS_PRODUCTES, $pdo),
            qi(Tables::DB_COMPTABILITAT_CATALEG_PRODUCTES, $pdo)
        );

        $productes = $db->getData(
            $queryProductes,
            [':factura_id' => $result['id']],
            false
        );


        // ---------------------------------------------------------
        // 3. RESPUESTA
        // ---------------------------------------------------------

        Response::success(
            message: MissatgesAPI::success('get'),
            data: [
                'factura' => $result,
                'productes' => $productes
            ],
            httpCode: 200
        );
    } catch (PDOException $e) {

        Response::error(
            MissatgesAPI::error('errorBD'),
            [
                'message' => $e->getMessage()
            ],
            500
        );
    }


    // GET : Llistat despeses
    // ruta => "https://elliot.cat/api/comptabilitat/get/despeses?receptor_id={id}&tipus_despesa={personal|professional}"
} else if ($slug === 'despeses') {

    AuthFactory::admin()->handle();

    $receptor_id = isset($_GET['receptor_id']) ? $_GET['receptor_id'] : null;
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

            pc.nom AS proveidorNom,
            pc.empresa AS proveidorEmpresa,
            p.id AS proveidorId,

            c.nom AS nomCategoria,
            s.nom AS nomSubCategoria

        FROM %s AS d
        LEFT JOIN %s AS p ON d.proveidor_id = p.id
        LEFT JOIN %s AS pc ON p.contacte_id = pc.id
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
        qi(Tables::DB_CONTACTES, $pdo),
        qi(Tables::DB_COMPTABILITAT_CATEGORIES_DESPESA, $pdo),
        qi(Tables::DB_COMPTABILITAT_SUBCATEGORIES_DESPESA, $pdo)
    );

    try {

        $params = [
            'tipus_despesa' => $tipus_despesa,
            'receptor_id' => Uuid::toBinary($receptor_id)
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

    AuthFactory::admin()->handle();

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

    AuthFactory::admin()->handle();

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

    AuthFactory::admin()->handle();

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

    AuthFactory::admin()->handle();

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

    AuthFactory::admin()->handle();

    AuthFactory::admin()->handle();

    $sql = <<<SQL
        SELECT
            c.id,
            c.nom,
            c.cognoms,
            c.empresa,
            c.email,
            c.web,
            c.nif,
            c.adreca,
            c.cp,
            c.ciutat_id,
            c.provincia_id,
            c.pais_id,
            c.tel_1,
            c.tel_2,
            p.contacte_id,
            p.created_at,
            p.updated_at,
            prov.provincia_ca,
            pa.pais_ca,
            ci.ciutat_ca
        FROM %s AS p
        INNER JOIN %s AS c ON p.contacte_id = c.id
        LEFT JOIN %s AS prov ON c.provincia_id = prov.id
        LEFT JOIN %s AS pa ON c.pais_id = pa.id
        LEFT JOIN %s AS ci ON c.ciutat_id = ci.id
        ORDER BY c.nom ASC
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_PROVEIDORS, $pdo),
        qi(Tables::DB_CONTACTES, $pdo),
        qi(Tables::DB_PROVINCIES, $pdo),
        qi(Tables::DB_PAISOS, $pdo),
        qi(Tables::DB_CIUTATS, $pdo),
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

    AuthFactory::admin()->handle();

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

    AuthFactory::admin()->handle();

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

    AuthFactory::admin()->handle();

    $sql = <<<SQL
            SELECT 
            p.id, p.concepte, p.client_id, p.servei_id, p.estat_id, p.import, p.data, p.created_at, p.modified_at, c.id AS idClient, c.nom, c.cognoms, c.empresa, e.estat, s.producte, YEAR(p.data) AS any
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
