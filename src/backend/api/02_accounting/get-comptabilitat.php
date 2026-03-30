<?php

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;

$slug = $routeParams[0];
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

    $sql = <<<SQL
            SELECT c.id, c.clientNom, c.clientCognoms, c.clientEmail, c.clientWeb, c.clientNIF, c.clientEmpresa, c.clientAdreca, c.clientCP, uuid_bin_to_text(c.ciutat_id) AS ciutat_id, uuid_bin_to_text(c.provincia_id) AS provincia_id, uuid_bin_to_text(c.pais_id) AS pais_id, c.clientTelefon, c.clientRegistre, ci.ciutat_ca, co.pais_ca, cou.provincia_ca, c.clientStatus, s.estatNom
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

    $id = $_GET['id'];
    $sql = <<<SQL
            SELECT c.id, c.clientNom, c.clientCognoms, c.clientEmail, c.clientWeb, c.clientNIF, c.clientEmpresa, c.clientAdreca, c.clientCP, uuid_bin_to_text(c.ciutat_id) AS ciutat_id, uuid_bin_to_text(c.provincia_id) AS provincia_id, uuid_bin_to_text(c.pais_id) AS pais_id, c.clientTelefon, c.clientRegistre, ci.ciutat_ca, co.pais_ca, cou.provincia_ca, c.clientStatus, s.estatNom
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
        // 1️⃣ Obtenemos la factura principal
        $sqlFactura = <<<SQL
            SELECT 
                ic.id,
                ic.client_id,
                ic.concepte,
                ic.emissor_id,
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
                vt.ivaPercen,
                ist.estat AS estatNom,
                pt.tipus AS tipusNom,
                pt.notes AS metodeNotes,
                c.clientNom,
                c.clientCognoms,
                c.clientEmpresa
            FROM %s AS ic
            LEFT JOIN %s AS vt ON ic.tipus_iva = vt.id
            LEFT JOIN %s AS ist ON ist.id = ic.estat
            LEFT JOIN %s AS pt ON ic.metode_pagament = pt.id
            LEFT JOIN %s AS c ON ic.client_id = c.id
            WHERE ic.id = :id
            LIMIT 1
        SQL;

        $queryFactura = sprintf(
            $sqlFactura,
            qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS, $pdo),
            qi(Tables::DB_COMPTABILITAT_FACTURACIO_TIPUS_IVA, $pdo),
            qi(Tables::DB_COMPTABILITAT_FACTURACIO_ESTAT, $pdo),
            qi(Tables::DB_COMPTABILITAT_FACTURACIO_TIPUS_PAGAMENT, $pdo),
            qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo)
        );

        $factura = $db->getData($queryFactura, [':id' => $id], true);

        if (!$factura) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        // 2️⃣ Obtenemos los productos asociados POR NUMERO_FACTURA
        $sqlProductes = <<<SQL
            SELECT 
                p.id,
                p.factura_id,
                pd.producte,
                p.notes,
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

        $productes = $db->getData($queryProductes, [':numero_factura' => $factura['numero_factura']], false);

        // 3️⃣ Devolvemos todo junto
        Response::success(
            MissatgesAPI::success('get'),
            [
                'factura' => $factura,
                'productes' => $productes
            ],
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }
} else if (isset($_GET['type']) && $_GET['type'] == 'accounting-elliotfernandez-supplies-invoices') {
    global $conn;
    $data = array();
    $stmt = $conn->prepare(
        "SELECT s.id, s.facEmpresa, s.facConcepte, s.facData, s.facSubtotal, s.facImportIva, s.facTotal, s.facIva, s.facPagament, c.id AS idEmpresa, c.empresaNom, c.empresaNIF, c.empresaDireccio, co.country, vt.ivaPercen, pt.tipusNom, cos.clientEmpresa
                    FROM db_accounting_soletrade_invoices_suppliers AS s
                    INNER JOIN db_accounting_hispantic_supplier_companies as c ON s.facEmpresa = c.id
                    INNER JOIN db_countries AS co ON c.empresaPais = co.id
                    INNER JOIN db_accounting_hispantic_vat_type AS vt ON s.facIva = vt.id
                    INNER JOIN db_accounting_hispantic_payment_type AS pt ON s.facPagament = pt.id
                    LEFT JOIN db_accounting_hispantic_costumers AS cos ON s.clientVinculat = cos.id
                    ORDER BY s.facData DESC"
    );
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    header('Content-Type: application/json');
    echo json_encode($data);
} else if ($slug === 'facturaClientsPDF') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        Response::error(MissatgesAPI::error('validacio'), ['id invàlid'], 400);
    }

    $sql = <<<SQL
        SELECT
            ic.id, ic.idUser, ic.facConcepte, ic.facData,
            YEAR(ic.facData) AS yearInvoice,
            ic.facDueDate, ic.facSubtotal, ic.facFees, ic.facTotal, ic.facVAT,
            ic.facIva, ic.facEstat, ic.facPaymentType,
            vt.ivaPercen, ist.estat, pt.tipusNom, pt.id AS idPayment,
            c.clientNom, c.clientCognoms, c.clientEmpresa, c.clientEmail, c.clientWeb,
            c.clientNIF, c.clientAdreca, ciu.ciutat AS clientCiutat,
            pro.provincia_ca AS clientProvincia, pa.pais_ca AS clientPais, c.clientCP
        FROM %s AS ic
        LEFT JOIN %s AS vt  ON ic.facIva = vt.id
        LEFT JOIN %s AS ist ON ist.id = ic.facEstat
        LEFT JOIN %s AS pt  ON ic.facPaymentType = pt.id
        LEFT JOIN %s AS c   ON ic.idUser = c.id
        LEFT JOIN %s AS ciu ON ciu.id = c.ciutat_id 
        LEFT JOIN %s AS pro ON pro.id = c.provincia_id
        LEFT JOIN %s AS pa  ON pa.id = c.pais_id
        WHERE ic.id = :id
    SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS, $pdo),
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_TIPUS_IVA, $pdo),
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_ESTAT, $pdo),
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_TIPUS_PAGAMENT, $pdo),
        qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo),
        qi(Tables::DB_CIUTATS, $pdo),
        qi(Tables::DB_PROVINCIES, $pdo),
        qi(Tables::DB_PAISOS, $pdo),
    );

    try {
        /** @var PDO $conn */
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Recopilar los resultados
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($data);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }
} else if ($slug === 'facturaProductesPDF') {
    $id = $_GET['id'];
    global $conn;

    $sql = <<<SQL
                SELECT p.id, p.factura_id, pd.producte, p.notes, p.preu
                        FROM %s AS p
                        LEFT JOIN %s AS pd ON pd.id = p.producte_id
                        WHERE p.factura_id = :id
                        GROUP BY p.id
                        ORDER BY p.preu DESC
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS_PRODUCTES, $pdo),
        qi(Tables::DB_COMPTABILITAT_CATALEG_PRODUCTES, $pdo)
    );

    try {
        /** @var PDO $conn */
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Recopilar los resultados
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($data);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }
} else if (isset($_GET['type']) && $_GET['type'] == 'accounting-supplies-invoices') {
    global $conn;
    $data = array();
    $stmt = $conn->prepare(
        "SELECT s.id, s.facEmpresa, s.facConcepte, s.facData, s.facSubtotal, s.facImportIva, s.facTotal, s.facIva, s.facPagament, s.loanDirectors, c.id AS idEmpresa, c.empresaNom, c.empresaNIF, c.empresaDireccio, co.country, vt.ivaPercen, pt.tipusNom
            FROM db_accounting_hispantic_invoices_suppliers AS s
            INNER JOIN db_accounting_hispantic_supplier_companies as c ON s.facEmpresa = c.id
            INNER JOIN db_countries AS co ON c.empresaPais = co.id
            INNER JOIN db_accounting_hispantic_vat_type AS vt ON s.facIva = vt.id
            INNER JOIN db_accounting_hispantic_payment_type AS pt ON s.facPagament = pt.id
            ORDER BY s.id ASC"
    );
    $stmt->execute();
    if ($stmt->rowCount() === 0) echo ('No rows');
    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $users;
    }
    header('Content-Type: application/json');
    echo json_encode($data);

    // GET : Llistat d'emissors
    // ruta => "https://elliot.cat/api/comptabilitat/get/emissors"
} else if ($slug === 'emissors') {

    $sql = <<<SQL
        SELECT 
            e.id, 
            e.nom, 
            e.nif, 
            e.numero_iva, 
            uuid_bin_to_text(e.pais) as pais_id, 
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

    $emissor_id = (int) $_GET['id'];

    $sql = <<<SQL
        SELECT e.id, e.nom, e.nif, e.numero_iva, uuid_bin_to_text(e.pais) AS pais, p.pais_ca, e.adreca, e.telefon, e.email
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
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
