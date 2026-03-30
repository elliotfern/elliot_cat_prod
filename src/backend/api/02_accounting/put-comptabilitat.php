<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;
use App\Config\Audit;
use App\Utils\ValidacioErrors;
use App\Config\DatabaseConnection;

$slug = $routeParams[0];

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

// Check if the request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Requiere ADMIN por token (user_type === 1)
if (!isAuthenticatedAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autoritzat (admin requerit)']);
    exit;
}

$conn = DatabaseConnection::getConnection();
$userUuid = getAuthenticatedUserUuid(); // para auditoría, si la soportas

if (!$conn) {
    die("No se pudo establecer conexión a la base de datos.");
}

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: PUT");

if ($slug === 'clients') {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    // Helpers (mismos que en POST)
    // Helpers corregidos para PHP 8+
    $trimOrNull = static fn($v): ?string => ($v === null) ? null : (trim((string)$v) ?: null);
    $toIntOrNull = static fn($v): ?int => is_numeric($v) ? (int)$v : null;
    $dateOrNull = static fn($v): ?string => (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) ? $v : null;
    $toDecimal = static fn($v): ?string => ($v === null) ? null : (preg_match('/^-?\d+(\.\d{1,4})?$/', str_replace(',', '.', str_replace([' ', "\u{00A0}"], '', (string)$v))) ? str_replace(',', '.', (string)$v) : null);

    // Datos (id requerido)
    $id             = (int)($data['id'] ?? 0);
    $clientNom      = $trimOrNull($data['clientNom'] ?? null);
    $clientCognoms  = $trimOrNull($data['clientCognoms'] ?? null);
    $clientEmail    = $trimOrNull($data['clientEmail'] ?? null);
    $clientWeb      = $trimOrNull($data['clientWeb'] ?? null);
    $clientNIF      = $trimOrNull($data['clientNIF'] ?? null);
    $clientEmpresa  = $trimOrNull($data['clientEmpresa'] ?? null);
    $clientAdreca   = $trimOrNull($data['clientAdreca'] ?? null);
    $clientCP       = $trimOrNull($data['clientCP'] ?? null);

    $pais_id        = $uuidOrNull($data['pais_id'] ?? null);
    $provincia_id   = $uuidOrNull($data['provincia_id'] ?? null);
    $ciutat_id      = $uuidOrNull($data['ciutat_id'] ?? null);

    $clientTelefon  = $trimOrNull($data['clientTelefon'] ?? null);
    $clientStatus   = $toIntOrNull($data['clientStatus'] ?? 1) ?? 1;
    $clientRegistre = $dateOrNull($data['clientRegistre'] ?? null);

    // Validación
    $errors = [];
    if ($id <= 0) {
        $errors[] = ValidacioErrors::requerit('id');
    }
    if ($clientNom === null) {
        $errors[] = ValidacioErrors::requerit('clientNom');
    } elseif (mb_strlen($clientNom) > 255) {
        $errors[] = ValidacioErrors::massaLlarg('clientNom', 255);
    }
    if ($clientEmail !== null && !filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = ValidacioErrors::invalid('clientEmail');
    }
    if ($clientWeb !== null) {
        if (mb_strlen($clientWeb) > 255) {
            $errors[] = ValidacioErrors::massaLlarg('clientWeb', 255);
        } elseif (!preg_match('~^https?://~i', $clientWeb)) {
            $clientWeb = 'https://' . $clientWeb;
        }
    }
    if ($clientNIF !== null && mb_strlen($clientNIF) > 20) {
        $errors[] = ValidacioErrors::massaLlarg('clientNIF', 20);
    }
    if ($clientCP !== null && mb_strlen($clientCP) > 10) {
        $errors[] = ValidacioErrors::massaLlarg('clientCP', 10);
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        global $conn, $userUuid;
        /** @var PDO $conn */
        $conn->beginTransaction();

        // Comprobar existencia
        $chk = $conn->prepare("SELECT 1 FROM db_comptabilitat_clients WHERE id = :id");
        $chk->bindValue(':id', $id, PDO::PARAM_INT);
        $chk->execute();
        if (!$chk->fetchColumn()) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('not_found'), ["Client id {$id} no existeix"], 404);
        }

        $sql = "UPDATE db_comptabilitat_clients
                   SET clientNom = :clientNom,
                       clientCognoms = :clientCognoms,
                       clientEmail = :clientEmail,
                       clientWeb = :clientWeb,
                       clientNIF = :clientNIF,
                       clientEmpresa = :clientEmpresa,
                       clientAdreca = :clientAdreca,
                       clientCP = :clientCP,
                       ciutat_id = uuid_text_to_bin(NULLIF(:ciutat_id, '')),
                       provincia_id = uuid_text_to_bin(NULLIF(:provincia_id, '')),
                       pais_id = uuid_text_to_bin(NULLIF(:pais_id, '')),
                       clientTelefon = :clientTelefon,
                       clientStatus = :clientStatus,
                       clientRegistre = :clientRegistre
                 WHERE id = :id";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':clientNom', $clientNom, PDO::PARAM_STR);
        $stmt->bindValue(':clientCognoms', $clientCognoms, $clientCognoms !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':clientEmail', $clientEmail, $clientEmail !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':clientWeb', $clientWeb, $clientWeb !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':clientNIF', $clientNIF, $clientNIF !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':clientEmpresa', $clientEmpresa, $clientEmpresa !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':clientAdreca', $clientAdreca, $clientAdreca !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':clientCP', $clientCP, $clientCP !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

        $stmt->bindValue(':ciutat_id', $ciutat_id, $ciutat_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':provincia_id', $provincia_id, $provincia_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':pais_id', $pais_id, $pais_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

        $stmt->bindValue(':clientTelefon', $clientTelefon, $clientTelefon !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':clientStatus', $clientStatus, PDO::PARAM_INT);
        $stmt->bindValue(':clientRegistre', $clientRegistre, $clientRegistre !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        // Auditoría
        $detalls = sprintf("Actualització client: %s (%s)", $clientNom, $clientEmail ?? '-');
        Audit::registrarCanvi($conn, $userUuid, "UPDATE", $detalls, 'db_comptabilitat_clients', $id);

        $conn->commit();

        Response::success(MissatgesAPI::success('update'), ['id' => $id], 200);
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }
} else if ($slug === 'facturaClient' && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    // Helpers
    $trimOrNull = static fn($v): ?string => $v === null ? null : (trim((string)$v) ?: null);
    $toIntOrNull = static fn($v): ?int => is_numeric($v) ? (int)$v : null;
    $dateOrNull = static fn($v): ?string => (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) ? $v : null;
    $toDecimal = static fn($v): ?string => $v === null ? null : (preg_match('/^-?\d+(\.\d{1,4})?$/', str_replace(',', '.', str_replace([' ', "\u{00A0}"], '', (string)$v))) ? str_replace(',', '.', (string)$v) : null);

    // --- Campos según la BD ---
    $id             = $toIntOrNull($data['id'] ?? null);
    $numero_factura = $trimOrNull($data['numero_factura'] ?? null);
    $emissor_id     = $toIntOrNull($data['emissor_id'] ?? null);
    $client_id      = $toIntOrNull($data['client_id'] ?? null);
    $concepte       = $trimOrNull($data['concepte'] ?? null);
    $data_factura   = $dateOrNull($data['data_factura'] ?? null);
    $data_venciment = $dateOrNull($data['data_venciment'] ?? null);
    $base_imposable = $toDecimal($data['base_imposable'] ?? null);
    $despeses_extra = $toDecimal($data['despeses_extra'] ?? 0);
    $total_factura  = $toDecimal($data['total_factura'] ?? null);
    $import_iva     = $toDecimal($data['import_iva'] ?? null);
    $tipus_iva      = $toIntOrNull($data['tipus_iva'] ?? null);
    $estat          = $toIntOrNull($data['estat'] ?? null);
    $metode_pagament = $toIntOrNull($data['metode_pagament'] ?? null);
    $notes          = $trimOrNull($data['notes'] ?? null);
    $projecte_id    = $toIntOrNull($data['projecte_id'] ?? null);
    $arxiu_url      = $trimOrNull($data['arxiu_url'] ?? null);
    $recurrent      = isset($data['recurrent']) ? (int)$data['recurrent'] : 0;
    $frequencia     = $recurrent ? $trimOrNull($data['frequencia'] ?? null) : null;
    $productes      = $data['productes'] ?? [];

    // --- Validación ---
    $errors = [];
    if ($id === null)             $errors[] = ValidacioErrors::requerit('id');
    if ($client_id === null)      $errors[] = ValidacioErrors::requerit('client_id');
    if ($data_factura === null)   $errors[] = ValidacioErrors::dataNoValida('data_factura');
    if ($data_venciment === null) $errors[] = ValidacioErrors::dataNoValida('data_venciment');
    if ($base_imposable === null) $errors[] = ValidacioErrors::requerit('base_imposable');
    if ($total_factura === null)  $errors[] = ValidacioErrors::requerit('total_factura');
    if ($import_iva === null)     $errors[] = ValidacioErrors::requerit('import_iva');
    if ($tipus_iva === null)      $errors[] = ValidacioErrors::requerit('tipus_iva');
    if ($metode_pagament === null) $errors[] = ValidacioErrors::requerit('metode_pagament');

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        global $conn, $userUuid;
        $conn->beginTransaction();

        // --- Update factura ---
        $sql = "UPDATE db_comptabilitat_facturacio_clients
                SET emissor_id = :emissor_id,
                    client_id = :client_id,
                    concepte = :concepte,
                    data_factura = :data_factura,
                    data_venciment = :data_venciment,
                    base_imposable = :base_imposable,
                    despeses_extra = :despeses_extra,
                    total_factura = :total_factura,
                    import_iva = :import_iva,
                    tipus_iva = :tipus_iva,
                    estat = :estat,
                    metode_pagament = :metode_pagament,
                    notes = :notes,
                    projecte_id = :projecte_id,
                    arxiu_url = :arxiu_url,
                    recurrent = :recurrent,
                    frequencia = :frequencia
                WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id'             => $id,
            ':emissor_id'     => $emissor_id,
            ':client_id'      => $client_id,
            ':concepte'       => $concepte,
            ':data_factura'   => $data_factura,
            ':data_venciment' => $data_venciment,
            ':base_imposable' => $base_imposable,
            ':despeses_extra' => $despeses_extra,
            ':total_factura'  => $total_factura,
            ':import_iva'     => $import_iva,
            ':tipus_iva'      => $tipus_iva,
            ':estat'          => $estat,
            ':metode_pagament' => $metode_pagament,
            ':notes'          => $notes,
            ':projecte_id'    => $projecte_id,
            ':arxiu_url'      => $arxiu_url,
            ':recurrent'      => $recurrent,
            ':frequencia'     => $frequencia,
        ]);

        // --- Productos ---
        $conn->prepare("DELETE FROM db_comptabilitat_facturacio_clients_productes WHERE factura_id = :id")
            ->execute([':id' => $id]);

        if (!empty($productes)) {
            $sqlProd = "INSERT INTO db_comptabilitat_facturacio_clients_productes
                        (factura_id, producte_id, descripcio, preu)
                        VALUES (:factura_id, :producte_id, :descripcio, :preu)";
            $stmtProd = $conn->prepare($sqlProd);

            foreach ($productes as $p) {
                $stmtProd->execute([
                    ':factura_id' => $id,
                    ':producte_id' => $toIntOrNull($p['producte_id'] ?? null),
                    ':descripcio' => $trimOrNull($p['descripcio'] ?? null),
                    ':preu'       => $toDecimal($p['preu'] ?? null),
                ]);
            }
        }

        // --- Auditoría ---
        $detalls = sprintf("Actualització factura client=%d concepte=%s data=%s", $client_id, $concepte, $data_factura);
        Audit::registrarCanvi($conn, $userUuid, "UPDATE", $detalls, 'db_comptabilitat_facturacio_clients', $id);

        $conn->commit();
        Response::success(MissatgesAPI::success('update'), ['id' => $id], 200);
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
