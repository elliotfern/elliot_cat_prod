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
                c.created_at,
                c.updated_at,

                cc.id AS client_id,
                cc.estat_id,

                e.num,
                e.estat,

                p.provincia,
                pa.pais,
                ci.ciutat

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

            p.provincia,
            pa.pais,
            ci.ciutat

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

    $id = $_GET['id'] ?? null;

    if (!$id) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['Falta el paràmetre id'],
            400
        );
        return;
    }

    try {

        /*
     * Convertim l'UUID del client a BINARY(16)
     */
        $clientIdBinary = uuid::toBinary($id);

        /*
     * -------------------------------------------------------
     * Pressupostos del client
     * -------------------------------------------------------
     */

        $sql = <<<SQL
        SELECT
            p.id,
            p.concepte,
            p.client_id,
            p.servei_id,
            p.estat_id,
            p.import,
            p.data,
            p.created_at,
            p.modified_at,

            c.id AS contacte_id,

            e.estat,

            s.producte,

            YEAR(p.data) AS any

        FROM %s AS p

        LEFT JOIN %s AS c
            ON p.client_id = c.id

        LEFT JOIN %s AS e
            ON p.estat_id = e.id

        LEFT JOIN %s AS s
            ON p.servei_id = s.id

        WHERE p.client_id = :id

        ORDER BY p.data DESC
    SQL;

        $query = sprintf(
            $sql,
            qi(Tables::DB_COMPTABILITAT_PRESSUPOSTOS, $pdo),
            qi(Tables::DB_CONTACTES, $pdo),
            qi(Tables::DB_COMPTABILITAT_CLIENTS_ESTAT, $pdo),
            qi(Tables::DB_COMPTABILITAT_CATALEG_PRODUCTES, $pdo)
        );

        /*
     * -------------------------------------------------------
     * Executar
     * -------------------------------------------------------
     */

        $params = [
            ':id' => $clientIdBinary
        ];

        $result = $db->getData(
            $query,
            $params,
            false
        );

        /*
     * -------------------------------------------------------
     * Resposta
     *
     * Si no hi ha pressupostos, retornem array buit.
     * NO és un error.
     * -------------------------------------------------------
     */

        Response::success(
            message: MissatgesAPI::success('get'),
            data: [
                'pressupostos' => $result ?? []
            ],
            httpCode: 200
        );
    } catch (PDOException $e) {

        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    } catch (Throwable $e) {

        Response::error(
            MissatgesAPI::error('validacio'),
            [$e->getMessage()],
            400
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
            c.id AS idClient, c.nom AS clientNom, c.cognoms AS clientCognoms, c.email AS clientEmail, c.empresa AS clientEmpresa, e.estat, s.producte, YEAR(p.data) AS any
            FROM %s AS p
            LEFT JOIN %s AS c ON p.client_id = c.id
            LEFT JOIN %s AS e ON p.estat_id = e.id
            LEFT JOIN %s AS s ON p.servei_id = s.id
            WHERE p.id = :id
            LIMIT 1
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_PRESSUPOSTOS, $pdo),
        qi(Tables::DB_CONTACTES, $pdo),
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

    $id = $_GET['id'] ?? null;

    if (!$id) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['Falta el paràmetre id'],
            400
        );
    }

    try {

        /*
         * Convertim l'UUID del client a BINARY(16)
         */
        $clientIdBinary = uuid::toBinary($id);

        /*
         * -------------------------------------------------------
         * Factures del client
         * -------------------------------------------------------
         */

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
                vt.ivaPercen,

                ic.estat AS estat_id,
                ist.estat AS estat,

                ic.metode_pagament,
                pt.tipus AS tipus,
                pt.notes AS metode_notes,

                ic.notes,
                ic.projecte_id,
                ic.arxiu_url,

                ic.recurrent,
                ic.frequencia,

                c.nom AS clientNom,
                c.cognoms AS clientCognoms,
                c.empresa AS clientEmpresa

            FROM %s AS ic

            LEFT JOIN %s AS vt
                ON ic.tipus_iva = vt.id

            LEFT JOIN %s AS ist
                ON ist.id = ic.estat

            LEFT JOIN %s AS pt
                ON ic.metode_pagament = pt.id

            LEFT JOIN %s AS c
                ON ic.client_id = c.id

            WHERE ic.client_id = :id

            ORDER BY ic.data_factura DESC
        SQL;

        $query = sprintf(
            $sql,
            qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS, $pdo),
            qi(Tables::DB_COMPTABILITAT_FACTURACIO_TIPUS_IVA, $pdo),
            qi(Tables::DB_COMPTABILITAT_FACTURACIO_ESTAT, $pdo),
            qi(Tables::DB_COMPTABILITAT_FACTURACIO_TIPUS_PAGAMENT, $pdo),
            qi(Tables::DB_CONTACTES, $pdo)
        );

        /*
         * -------------------------------------------------------
         * Total facturat pel client
         * -------------------------------------------------------
         */

        $sql2 = <<<SQL
            SELECT
                COALESCE(SUM(ic.total_factura), 0) AS total_facturat
            FROM %s AS ic
            WHERE ic.client_id = :id
        SQL;

        $query2 = sprintf(
            $sql2,
            qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS, $pdo)
        );

        /*
         * -------------------------------------------------------
         * Executar
         * -------------------------------------------------------
         */

        $params = [
            ':id' => $clientIdBinary
        ];

        $factures = $db->getData(
            $query,
            $params,
            false
        );

        $totalRow = $db->getData(
            $query2,
            $params,
            false
        );

        $total = $totalRow[0]['total_facturat'] ?? 0;

        /*
         * -------------------------------------------------------
         * Si no hi ha factures
         * -------------------------------------------------------
         *
         * No és un error.
         * Retornem una resposta correcta amb array buit.
         */

        Response::success(
            MissatgesAPI::success('get'),
            [
                'factures' => $factures ?? [],
                'totals' => [
                    'total_facturat' => (float) $total
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
    } catch (Throwable $e) {

        Response::error(
            MissatgesAPI::error('validacio'),
            [$e->getMessage()],
            400
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
        LEFT JOIN %s AS vt ON ic.tipus_iva = vt.id
        LEFT JOIN %s AS ist ON ist.id = ic.estat
        LEFT JOIN %s AS pt ON ic.metode_pagament = pt.id
        LEFT JOIN %s AS cl ON ic.client_id = cl.contacte_id
        LEFT JOIN %s AS c ON cl.contacte_id = c.id
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
    // ruta => "https://elliot.cat/api/comptabilitat/get/facturaCompleta?id={uuid}"
} else if ($slug === 'facturaCompleta') {

    $id = isset($_GET['id']) ? $_GET['id'] : null;

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

                ic.estat AS estat_id,
                ic.metode_pagament,

                ic.notes,
                ic.projecte_id,
                ic.arxiu_url,
                ic.recurrent,
                ic.frequencia,

                vt.ivaPercen,

                ist.estat,

                pt.tipus,
                pt.notes,

                -- CONTACTE DEL CLIENT
                co.nom,
                co.cognoms,
                co.empresa,
                co.email,
                co.web,
                co.nif,
                co.adreca,
                co.cp,

                ciu.ciutat,
                pro.provincia,
                pa.pais,

                -- EMISSOR
                e.nom AS nomEmissor,
                e.nif AS nifEmissor,
                e.numero_iva,
                e.adreca AS adrecaEmissor,
                e.telefon AS telefonEmissor,
                e.email AS emailEmissor,

                pai.pais AS paisEmissor

            FROM %s AS ic
            LEFT JOIN %s AS vt ON ic.tipus_iva = vt.id
            LEFT JOIN %s AS ist ON ic.estat = ist.id
            LEFT JOIN %s AS pt ON ic.metode_pagament = pt.id

            -- CLIENT
            LEFT JOIN %s AS c ON ic.client_id = c.contacte_id

            -- CONTACTE
            LEFT JOIN %s AS co ON c.contacte_id = co.id

            -- CIUTAT DEL CONTACTE
            LEFT JOIN %s AS ciu ON co.ciutat_id = ciu.id

            -- PROVÍNCIA DEL CONTACTE
            LEFT JOIN %s AS pro ON co.provincia_id = pro.id

            -- PAÍS DEL CONTACTE
            LEFT JOIN %s AS pa ON co.pais_id = pa.id

            -- EMISSOR
            LEFT JOIN %s AS e ON ic.emissor_id = e.id

            -- PAÍS DE L'EMISSOR
            LEFT JOIN %s AS pai ON e.pais_id = pai.id

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
            [':id' => Uuid::toBinary($id)],
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
    // ruta => "api/comptabilitat/get/emissors"
} else if ($slug === 'emissors') {

    AuthFactory::admin()->handle();

    $sql = <<<SQL
        SELECT 
            e.id, 
            e.nom, 
            e.nif, 
            e.numero_iva, 
            e.pais_id, 
            p.pais,
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

    // GET : Llistat d'emissors per comptabilitat
    // ruta => "api/comptabilitat/get/emissorsComptabilitat"
} else if ($slug === 'emissorsComptabilitat') {

    AuthFactory::admin()->handle();

    $sql = <<<SQL
        SELECT
            e.id,
            e.nom,
            e.dataInici,
            e.dataFi
        FROM %s AS e
        WHERE e.id <> :emissor_personal
        ORDER BY e.dataInici DESC
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_EMISSORS, $pdo)
    );

    try {

        $params = [
            ':emissor_personal' => uuid::toBinary(
                '019e3ebaf71370c2860a40a7a241e107'
            ),
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

    // GET : Obtenir emissor per ID
    // ruta => "api/comptabilitat/get/emissorId?id={id}"
} else if ($slug === 'emissorId') {

    AuthFactory::admin()->handle();

    $emissor_id = $_GET['id'];

    $sql = <<<SQL
        SELECT e.id, e.nom, e.nif, e.numero_iva, p.pais, e.adreca, e.telefon, e.email, e.pais_id, e.dataInici, e.dataFi
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
} else if ($slug === 'producteId') {

    AuthFactory::admin()->handle();

    $producte_id = $_GET['id'];

    $sql = <<<SQL
        SELECT id, producte, descripcio, actiu, unitat, preu_recomanat
        FROM %s
        WHERE id = :id
        LIMIT 1
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_CATALEG_PRODUCTES, $pdo)
    );

    try {

        $params = [':id' => Uuid::toBinary($producte_id)];
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
            c.created_at,
            c.updated_at,
            prov.provincia,
            pa.pais,
            ci.ciutat
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
            c.id,
            c.nom,
            c.nif,
            c.adreca,
            c.ciutat_id,
            c.cp,
            c.pais_id,
            c.tel_1,
            c.tel_2,
            c.email,
            c.web,
            p.contacte_id,
            c.created_at,
            c.updated_at
        FROM %s AS c
        LEFT JOIN %s AS p ON c.id = p.contacte_id
        WHERE c.id = :id
        LIMIT 1
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_CONTACTES, $pdo),
        qi(Tables::DB_COMPTABILITAT_PROVEIDORS, $pdo),
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

    $despesa_id = isset($_GET['id']) ? $_GET['id'] : null;

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
        $params = ['id' => Uuid::toBinary($despesa_id)];
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
            p.id, p.concepte, p.client_id, p.servei_id, p.estat_id, p.import, p.data, p.created_at, p.modified_at, c.id AS idClient, c.nom, c.cognoms, c.empresa, e.estat, s.producte, YEAR(p.data) AS any, e.num
            FROM %s AS p
            LEFT JOIN %s AS c ON p.client_id = c.id
            LEFT JOIN %s AS e ON p.estat_id = e.id
            LEFT JOIN %s AS s ON p.servei_id = s.id
            ORDER BY p.data DESC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_PRESSUPOSTOS, $pdo),
        qi(Tables::DB_CONTACTES, $pdo),
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

    // GET : Resum comptable
    // ruta => "api/comptabilitat/get/resum?emissor_id={id}&exercici=2026"
} else if ($slug === 'resum') {
    AuthFactory::admin()->handle();

    $emissor_id = $_GET['emissor_id'] ?? null;
    $exercici = isset($_GET['exercici'])
        ? (int) $_GET['exercici']
        : null;

    if (!$emissor_id || !$exercici) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['emissor_id i exercici són obligatoris'],
            400
        );
        return;
    }

    $dataIniciExercici = sprintf('%04d-01-01', $exercici);
    $dataFiExercici = sprintf('%04d-12-31', $exercici);

    $sql = <<<SQL
        SELECT
            e.dataInici,
            e.dataFi,

            COALESCE(
                (
                    SELECT SUM(f.total_factura)
                    FROM %s AS f
                    WHERE f.emissor_id = e.id
                      AND f.data_factura BETWEEN
                          GREATEST(e.dataInici, :data_inici_exercici_ingressos)
                          AND
                          LEAST(e.dataFi, :data_fi_exercici_ingressos)
                ),
                0
            ) AS ingressos,

            COALESCE(
                (
                    SELECT SUM(d.total)
                    FROM %s AS d
                    WHERE d.receptor_id = e.id
                      AND d.pagat = 1
                      AND d.data_pagament IS NOT NULL
                      AND d.data_pagament BETWEEN
                          GREATEST(e.dataInici, :data_inici_exercici_despeses)
                          AND
                          LEAST(e.dataFi, :data_fi_exercici_despeses)
                ),
                0
            ) AS despeses

        FROM %s AS e
        WHERE e.id = :emissor_id
        LIMIT 1
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS, $pdo),
        qi(Tables::DB_COMPTABILITAT_DESPESES, $pdo),
        qi(Tables::DB_COMPTABILITAT_EMISSORS, $pdo)
    );

    try {

        $params = [
            ':emissor_id' => uuid::toBinary($emissor_id),

            ':data_inici_exercici_ingressos' => $dataIniciExercici,
            ':data_fi_exercici_ingressos' => $dataFiExercici,

            ':data_inici_exercici_despeses' => $dataIniciExercici,
            ':data_fi_exercici_despeses' => $dataFiExercici,
        ];

        $result = $db->getData($query, $params, true);

        if (!$result) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        $ingressos = (float) $result['ingressos'];
        $despeses = (float) $result['despeses'];

        $resultat = round($ingressos - $despeses, 2);

        Response::success(
            message: MissatgesAPI::success('get'),
            data: [
                'exercici' => $exercici,
                'ingressos' => $ingressos,
                'despeses' => $despeses,
                'resultat' => $resultat,
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
    // GET : Obtenir evolució mensual de comptabilitat
    // ruta => "api/comptabilitat/get/evolucioMensual?emissor_id={id}&exercici={any}"
} else if ($slug === 'evolucioMensual') {

    AuthFactory::admin()->handle();

    $emissor_id = $_GET['emissor_id'] ?? null;
    $exercici = isset($_GET['exercici'])
        ? (int) $_GET['exercici']
        : null;

    if (!$emissor_id || !$exercici) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['emissor_id i exercici són obligatoris'],
            400
        );
        return;
    }

    $dataIniciExercici = sprintf('%04d-01-01', $exercici);
    $dataFiExercici = sprintf('%04d-12-31', $exercici);

    $sql = <<<SQL
        SELECT
            m.mes,

            COALESCE(
                (
                    SELECT SUM(f.total_factura)
                    FROM %s AS f
                    WHERE f.emissor_id = e.id
                      AND f.data_factura BETWEEN
                          GREATEST(e.dataInici, :data_inici_exercici_ingressos)
                          AND
                          LEAST(e.dataFi, :data_fi_exercici_ingressos)
                      AND MONTH(f.data_factura) = m.mes
                ),
                0
            ) AS ingressos,

            COALESCE(
                (
                    SELECT SUM(d.total)
                    FROM %s AS d
                    WHERE d.receptor_id = e.id
                      AND d.pagat = 1
                      AND d.data_pagament IS NOT NULL
                      AND d.data_pagament BETWEEN
                          GREATEST(e.dataInici, :data_inici_exercici_despeses)
                          AND
                          LEAST(e.dataFi, :data_fi_exercici_despeses)
                      AND MONTH(d.data_pagament) = m.mes
                ),
                0
            ) AS despeses

        FROM (
            SELECT 1 AS mes
            UNION ALL SELECT 2
            UNION ALL SELECT 3
            UNION ALL SELECT 4
            UNION ALL SELECT 5
            UNION ALL SELECT 6
            UNION ALL SELECT 7
            UNION ALL SELECT 8
            UNION ALL SELECT 9
            UNION ALL SELECT 10
            UNION ALL SELECT 11
            UNION ALL SELECT 12
        ) AS m

        INNER JOIN %s AS e
            ON e.id = :emissor_id

        ORDER BY m.mes ASC
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS, $pdo),
        qi(Tables::DB_COMPTABILITAT_DESPESES, $pdo),
        qi(Tables::DB_COMPTABILITAT_EMISSORS, $pdo)
    );

    try {

        $params = [
            ':emissor_id' => uuid::toBinary($emissor_id),

            ':data_inici_exercici_ingressos' => $dataIniciExercici,
            ':data_fi_exercici_ingressos' => $dataFiExercici,

            ':data_inici_exercici_despeses' => $dataIniciExercici,
            ':data_fi_exercici_despeses' => $dataFiExercici,
        ];

        $result = $db->getData($query, $params);

        $mesos = [];

        foreach ($result as $row) {

            $ingressos = round((float) $row['ingressos'], 2);
            $despeses = round((float) $row['despeses'], 2);
            $resultat = round($ingressos - $despeses, 2);

            $mesos[] = [
                'mes' => (int) $row['mes'],
                'ingressos' => $ingressos,
                'despeses' => $despeses,
                'resultat' => $resultat,
            ];
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: [
                'exercici' => $exercici,
                'mesos' => $mesos,
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
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    return;
}
