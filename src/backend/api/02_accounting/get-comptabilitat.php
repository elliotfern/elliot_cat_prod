<?php

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;

$slug = $routeParams[0];
$db = new Database();
$pdo = $db->getPdo();

// GET : Llistat clients
// ruta => "https://elliot.cat/api/comptabilitat/get/clients"
if ($slug === 'clients') {

    $sql = <<<SQL
            SELECT c.id, c.clientNom, c.clientCognoms, c.clientEmail, c.clientWeb, c.clientNIF, c.clientEmpresa, c.clientAdreca, c.clientCP, uuid_bin_to_text(c.ciutat_id) AS ciutat_id, uuid_bin_to_text(c.provincia_id,) AS provincia_id, uuid_bin_to_text(c.pais_id,) AS pais_id, c.clientTelefon, c.clientRegistre, ci.ciutat_ca, co.pais_ca, cou.provincia_ca, c.clientStatus, s.estatNom
            FROM %s AS c
            LEFT JOIN %s AS ci ON c.ciutat_id = ci.id
            LEFT JOIN %s AS co ON c.pais_id = co.id
            LEFT JOIN %s AS cou ON c.provincia_id = cou.id
            LEFT OIN %s AS s ON c.clientStatus = s.id
            ORDER BY c.clientRegistre DESC
            SQL;

    $sqlPerfil = sprintf(
        $sql,
        qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo),
        qi(Tables::DB_CIUTATS, $pdo),
        qi(Tables::DB_PROVINCIES, $pdo),
        qi(Tables::DB_PAISOS, $pdo),
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
} elseif (isset($_GET['type']) && $_GET['type'] == 'accounting-customers-invoices') {
    global $conn;

    $query = "SELECT ic.id, ic.idUser, ic.facConcepte, ic.facData, YEAR(ic.facData) AS yearInvoice,  ic.facDueDate, ic.facSubtotal, ic.facFees, ic.facTotal, ic.facVAT, ic.facIva, ic.facEstat, ic.facPaymentType, vt.ivaPercen, ist.estat, pt.tipusNom, c.clientNom, c.clientCognoms, c.clientEmpresa
            FROM db_accounting_hispantic_invoices_customers  AS ic
            INNER JOIN db_accounting_hispantic_vat_type AS vt ON ic.facIva = vt.id
            INNER JOIN  db_accounting_hispantic_invoices_status AS ist ON ist.id = ic.facEstat
            INNER JOIN db_accounting_hispantic_payment_type AS pt ON ic.facPaymentType = pt.id
            INNER JOIN db_accounting_hispantic_costumers AS c ON ic.idUser = c.id
            ORDER BY ic.id DESC";

    $stmt = $conn->prepare($query);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit();
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($data);
} elseif (isset($_GET['type']) && $_GET['type'] == 'accounting-elliotfernandez-customers-invoices') {
    global $conn;
    $query = "SELECT ic.id, ic.idUser, ic.facConcepte, ic.facData, YEAR(ic.facData) AS yearInvoice, CONCAT('Any ', YEAR(ic.facData)) AS any, ic.facDueDate, ic.facSubtotal, ic.facFees, ic.facTotal, ic.facVAT, ic.facIva, ic.facEstat, ic.facPaymentType, vt.ivaPercen, ist.estat, pt.tipusNom, c.clientNom, c.clientCognoms, c.clientEmpresa
                FROM db_accounting_soletrade_invoices_customers  AS ic
                LEFT JOIN db_accounting_hispantic_vat_type AS vt ON ic.facIva = vt.id
                LEFT JOIN db_accounting_hispantic_invoices_status AS ist ON ist.id = ic.facEstat
                LEFT JOIN db_accounting_hispantic_payment_type AS pt ON ic.facPaymentType = pt.id
                LEFT JOIN db_accounting_hispantic_costumers AS c ON ic.idUser = c.id
                ORDER BY ic.id DESC";

    $stmt = $conn->prepare($query);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit();
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($data);
} elseif (isset($_GET['type']) && $_GET['type'] == 'accounting-elliotfernandez-supplies-invoices') {
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
} elseif ((isset($_GET['type']) && $_GET['type'] == 'customers-invoices') && (isset($_GET['id']))) {
    $id = $_GET['id'];
    global $conn;
    $data = array();

    /** @var PDO $conn */
    $query = "SELECT ic.id, ic.idUser, ic.facConcepte, ic.facData, YEAR(ic.facData) AS yearInvoice,  ic.facDueDate, ic.facSubtotal, ic.facFees, ic.facTotal, ic.facVAT, ic.facIva, ic.facEstat, ic.facPaymentType, vt.ivaPercen, ist.estat, pt.tipusNom, pt.id AS idPayment, c.clientNom, c.clientCognoms, c.clientEmpresa, c.clientEmail, c.clientWeb, c.clientNIF, c.clientAdreca, ciu.city AS clientCiutat, pro.county AS clientProvincia, pa.country AS clientPais, c.clientCP
                    FROM db_accounting_soletrade_invoices_customers  AS ic
                    LEFT JOIN db_accounting_hispantic_vat_type AS vt ON ic.facIva = vt.id
                    LEFT JOIN db_accounting_hispantic_invoices_status AS ist ON ist.id = ic.facEstat
                    LEFT JOIN db_accounting_hispantic_payment_type AS pt ON ic.facPaymentType = pt.id
                    LEFT JOIN db_accounting_hispantic_costumers AS c ON ic.idUser = c.id
                    LEFT JOIN db_cities AS ciu ON ciu.id = c.clientCiutat
                    LEFT JOIN db_countries_counties AS pro ON pro.id = c.clientProvincia
                    LEFT JOIN db_countries AS pa ON pa.id = c.clientPais
                    WHERE ic.id = $id";

    $stmt = $conn->prepare($query);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit();
    }

    // Recopilar los resultados
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($data);
} elseif ((isset($_GET['type']) && $_GET['type'] == 'invoice-products') && (isset($_GET['id']))) {
    $id = $_GET['id'];
    global $conn;

    $query = "SELECT p.id, p.invoice, pd.product, p.notes, p.price
                        FROM db_accounting_soletrade_invoices_customers_products AS p
                        LEFT JOIN db_accounting_soletrade_products AS pd ON pd.id = p.product
                        WHERE p.invoice = $id
                        GROUP BY p.id
                        ORDER BY p.price desc";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit();
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($data);
} elseif (isset($_GET['type']) && $_GET['type'] == 'accounting-supplies-invoices') {
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
}
