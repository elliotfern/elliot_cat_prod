<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;
use App\Config\Audit;
use App\Utils\ValidacioErrors;
use App\Config\DatabaseConnection;

$slug = $routeParams[0];

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
header("Access-Control-Allow-Methods: POST");

/**
 * Genera el siguiente número de factura basado en el año.
 *
 * @param PDO $db Conexión PDO a la base de datos
 * @return string Número de factura en formato YYYY-SEQ-C (ej: 2026-01-C)
 */
function generarNumeroFactura(PDO $db): string
{
    $year = date('Y');

    // Consulta la última factura del año de la serie C
    $stmt = $db->prepare("
        SELECT numero_factura
        FROM db_comptabilitat_facturacio_clients
        WHERE numero_factura LIKE :yearPrefix
        AND numero_factura LIKE '%-C'
        ORDER BY id DESC
        LIMIT 1
    ");
    $yearPrefix = "$year-%";
    $stmt->execute([':yearPrefix' => $yearPrefix]);
    $ultima = $stmt->fetchColumn();

    if ($ultima) {
        // Extraer la parte secuencial
        $parts = explode('-', $ultima);
        $seq = (int)($parts[1] ?? 0);
        $seq++;
    } else {
        $seq = 1;
    }

    // Formato con dos dígitos en la secuencia y sufijo C
    return sprintf('%s-%03d-C', $year, $seq);
}

if ($slug === 'clients') {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    // Helpers
    $trimOrNull  = static fn($v): ?string => (is_string($v) && trim($v) !== '') ? trim($v) : null;
    $toIntOrNull = static fn($v): ?int    => (is_numeric($v) ? (int)$v : null);
    $isZeroUuid  = static fn($s): bool    => is_string($s) && preg_match('/^0{8}-0{4}-0{4}-0{4}-0{12}$/i', $s);
    $uuidOrNull  = static function ($v): ?string {
        if ($v === null || $v === '') return null;
        $s = is_string($v) ? trim($v) : '';
        if ($s === '' || preg_match('/^0{8}-0{4}-0{4}-0{4}-0{12}$/i', $s)) return null;
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s) ? strtolower($s) : null;
    };
    $dateOrNull  = static fn($v): ?string => (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) ? $v : null;

    // Datos
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
            $clientWeb = 'https://' . $clientWeb; // autocompleta esquema
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

        $conn->beginTransaction();

        $sql = "INSERT INTO db_comptabilitat_clients
                   (clientNom, clientCognoms, clientEmail, clientWeb, clientNIF, clientEmpresa, clientAdreca, clientCP,
                    ciutat_id, provincia_id, pais_id, clientTelefon, clientStatus, clientRegistre)
                VALUES
                   (:clientNom, :clientCognoms, :clientEmail, :clientWeb, :clientNIF, :clientEmpresa, :clientAdreca, :clientCP,
                    uuid_text_to_bin(NULLIF(:ciutat_id, '')),
                    uuid_text_to_bin(NULLIF(:provincia_id, '')),
                    uuid_text_to_bin(NULLIF(:pais_id, '')),
                    :clientTelefon, :clientStatus, :clientRegistre)";

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

        $stmt->execute();
        $newId = (int)$conn->lastInsertId();

        // Auditoría
        $detalls = sprintf("Creació client: %s (%s)", $clientNom, $clientEmail ?? '-');
        Audit::registrarCanvi($conn, $userUuid, "INSERT", $detalls, 'db_comptabilitat_clients', $newId);

        $conn->commit();

        Response::success(MissatgesAPI::success('create'), ['id' => $newId], 201);
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }
} else if ($slug === 'facturaClient') {
    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    // Helpers
    // Helpers corregidos para PHP 8+
    $trimOrNull = static fn($v): ?string => ($v === null) ? null : (trim((string)$v) ?: null);
    $toIntOrNull = static fn($v): ?int => is_numeric($v) ? (int)$v : null;
    $dateOrNull = static fn($v): ?string => (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) ? $v : null;
    $toDecimal = static fn($v): ?string => ($v === null) ? null : (preg_match('/^-?\d+(\.\d{1,4})?$/', str_replace(',', '.', str_replace([' ', "\u{00A0}"], '', (string)$v))) ? str_replace(',', '.', (string)$v) : null);

    // Datos (nombres ya alineados con BD)
    $emissor_id      = $toIntOrNull($data['emissor_id'] ?? null);
    $client_id       = $toIntOrNull($data['client_id'] ?? null);
    $concepte        = $trimOrNull($data['concepte'] ?? null);
    $data_factura    = $dateOrNull($data['data_factura'] ?? null);
    $data_venciment  = $dateOrNull($data['data_venciment'] ?? null);
    $base_imposable  = $toDecimal($data['base_imposable'] ?? null);
    $despeses_extra  = $toDecimal($data['despeses_extra'] ?? 0);
    $total_factura   = $toDecimal($data['total_factura'] ?? null);
    $import_iva      = $toDecimal($data['import_iva'] ?? null);
    $tipus_iva       = $toIntOrNull($data['tipus_iva'] ?? null);
    $estat           = $toIntOrNull($data['estat'] ?? null);
    $metode_pagament = $toIntOrNull($data['metode_pagament'] ?? null);
    $notes           = $trimOrNull($data['notes'] ?? null);
    $projecte_id     = $toIntOrNull($data['projecte_id'] ?? null);
    $arxiu_url       = $trimOrNull($data['arxiu_url'] ?? null);
    $recurrent = isset($data['recurrent']) ? (int)$data['recurrent'] : 0;
    $frequencia = $recurrent ? $trimOrNull($data['frequencia'] ?? null) : null;

    $detallsProductes = $data['productes'] ?? [];

    // Validación
    $errors = [];
    if ($emissor_id === null)      $errors[] = ValidacioErrors::requerit('emissor_id');
    if ($client_id === null)       $errors[] = ValidacioErrors::requerit('client_id');
    if ($concepte === null)        $errors[] = ValidacioErrors::requerit('concepte');
    if ($data_factura === null)    $errors[] = ValidacioErrors::dataNoValida('data_factura');
    if ($data_venciment === null)  $errors[] = ValidacioErrors::dataNoValida('data_venciment');
    if ($base_imposable === null)  $errors[] = ValidacioErrors::requerit('base_imposable');
    if ($total_factura === null)   $errors[] = ValidacioErrors::requerit('total_factura');
    if ($import_iva === null)      $errors[] = ValidacioErrors::requerit('import_iva');
    if ($tipus_iva === null)       $errors[] = ValidacioErrors::requerit('tipus_iva');
    if ($estat === null)            $errors[] = ValidacioErrors::requerit('estat');
    if ($metode_pagament === null) $errors[] = ValidacioErrors::requerit('metode_pagament');

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }


    try {
        global $conn, $userUuid;
        $conn->beginTransaction();

        $numero_factura = generarNumeroFactura($conn);

        // Inserta factura
        $sql = "INSERT INTO db_comptabilitat_facturacio_clients
              (numero_factura, emissor_id, client_id, concepte, data_factura, data_venciment,
               base_imposable, despeses_extra, total_factura, import_iva, tipus_iva, estat,
               metode_pagament, notes, projecte_id, arxiu_url, recurrent, frequencia)
            VALUES
              (:numero_factura, :emissor_id, :client_id, :concepte, :data_factura, :data_venciment,
               :base_imposable, :despeses_extra, :total_factura, :import_iva, :tipus_iva, :estat,
               :metode_pagament, :notes, :projecte_id, :arxiu_url, :recurrent, :frequencia)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':numero_factura' => $numero_factura,
            ':emissor_id' => $emissor_id,
            ':client_id' => $client_id,
            ':concepte' => $concepte,
            ':data_factura' => $data_factura,
            ':data_venciment' => $data_venciment,
            ':base_imposable' => $base_imposable,
            ':despeses_extra' => $despeses_extra,
            ':total_factura' => $total_factura,
            ':import_iva' => $import_iva,
            ':tipus_iva' => $tipus_iva,
            ':estat' => $estat,
            ':metode_pagament' => $metode_pagament,
            ':notes' => $notes,
            ':projecte_id' => $projecte_id,
            ':arxiu_url' => $arxiu_url,
            ':recurrent' => $recurrent,
            'frequencia' => $frequencia,
        ]);
        $newId = (int)$conn->lastInsertId();

        // Inserta productos
        if (!empty($detallsProductes)) {
            $sqlProd = "INSERT INTO db_comptabilitat_facturacio_clients_productes
                        (factura_id, producte_id, descripcio, preu)
                        VALUES (:factura_id, :producte_id, :descripcio, :preu)";
            $stmtProd = $conn->prepare($sqlProd);

            foreach ($detallsProductes as $p) {
                $stmtProd->execute([
                    ':factura_id' => $numero_factura,
                    ':producte_id' => $toIntOrNull($p['producte_id'] ?? null),
                    ':descripcio' => $trimOrNull($p['descripcio'] ?? null),
                    ':preu' => $toDecimal($p['preu'] ?? null)
                ]);
            }
        }

        // Auditoría
        Audit::registrarCanvi(
            $conn,
            $userUuid,
            "INSERT",
            sprintf("Creació factura client=%d concepte=%s data=%s", $client_id, $concepte, $data_factura),
            'db_comptabilitat_facturacio_clients',
            $newId
        );

        $conn->commit();
        Response::success(MissatgesAPI::success('create'), ['id' => $newId, 'numero_factura' => $numero_factura], 201);
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // POST : Crear nou emissor
} else if ($slug === 'emissor') {

    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    // Helpers
    $trimOrNull  = static fn($v): ?string => (is_string($v) && trim($v) !== '') ? trim($v) : null;
    $toIntOrNull = static fn($v): ?int    => (is_numeric($v) ? (int)$v : null);
    $uuidOrNull  = static function ($v): ?string {
        if ($v === null || $v === '') return null;
        $s = is_string($v) ? trim($v) : '';
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s) ? strtolower($s) : null;
    };
    $dateOrNull  = static fn($v): ?string => (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) ? $v : null;

    // Datos
    $nom       = $trimOrNull($data['nom'] ?? null);
    $nif       = $trimOrNull($data['nif'] ?? null);
    $numero_iva = $trimOrNull($data['numero_iva'] ?? null);
    $pais      = $uuidOrNull($data['pais'] ?? null);
    $adreca    = $trimOrNull($data['adreca'] ?? null);
    $telefon   = $trimOrNull($data['telefon'] ?? null);
    $email     = $trimOrNull($data['email'] ?? null);

    // Validación
    $errors = [];
    if ($nom === null) {
        $errors[] = ValidacioErrors::requerit('nom');
    } elseif (mb_strlen($nom) > 255) {
        $errors[] = ValidacioErrors::massaLlarg('nom', 255);
    }

    if ($nif !== null && mb_strlen($nif) > 20) {
        $errors[] = ValidacioErrors::massaLlarg('nif', 20);
    }
    if ($numero_iva !== null && mb_strlen($numero_iva) > 20) {
        $errors[] = ValidacioErrors::massaLlarg('numero_iva', 20);
    }
    if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = ValidacioErrors::invalid('email');
    }
    if ($telefon !== null && mb_strlen($telefon) > 20) {
        $errors[] = ValidacioErrors::massaLlarg('telefon', 20);
    }
    if ($adreca !== null && mb_strlen($adreca) > 255) {
        $errors[] = ValidacioErrors::massaLlarg('adreca', 255);
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        $conn->beginTransaction();

        $sql = "INSERT INTO db_comptabilitat_emissors
                   (nom, nif, numero_iva, pais, adreca, telefon, email, created_at, updated_at)
                VALUES
                   (:nom, :nif, :numero_iva, uuid_text_to_bin(NULLIF(:pais, '')), :adreca, :telefon, :email, NOW(), NOW())";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindValue(':nif', $nif, $nif !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':numero_iva', $numero_iva, $numero_iva !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':pais', $pais, $pais !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':adreca', $adreca, $adreca !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':telefon', $telefon, $telefon !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':email', $email, $email !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

        $stmt->execute();
        $newId = (int)$conn->lastInsertId();

        // Auditoría
        $detalls = sprintf("Creació emissor: %s (%s)", $nom, $email ?? '-');
        Audit::registrarCanvi($conn, $userUuid, "INSERT", $detalls, 'db_comptabilitat_emissors', $newId);

        $conn->commit();

        Response::success(MissatgesAPI::success('create'), ['id' => $newId], 201);
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // POST : Crear nuevo producto
    // ruta => "https://elliot.cat/api/comptabilitat/post/producte"
} else if ($slug === 'producte' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
        return;
    }

    // Helpers
    $trimOrNull  = static fn($v): ?string => (is_string($v) && trim($v) !== '') ? trim($v) : null;
    $toFloatOrNull = static fn($v): ?float => (is_numeric($v) ? (float)$v : null);
    $toIntOrNull = static fn($v): ?int => (is_numeric($v) ? (int)$v : null);

    // Datos
    $producte       = $trimOrNull($data['producte'] ?? null);
    $descripcio     = $trimOrNull($data['descripcio'] ?? null);
    $actiu          = $toIntOrNull($data['actiu'] ?? 1) ?? 1;
    $unitat         = $trimOrNull($data['unitat'] ?? null);
    $preu_recomanat = $toFloatOrNull($data['preu_recomanat'] ?? null);

    // Validación
    $errors = [];
    if ($producte === null) {
        $errors[] = ValidacioErrors::requerit('producte');
    } elseif (mb_strlen($producte) > 255) {
        $errors[] = ValidacioErrors::massaLlarg('producte', 255);
    }

    if ($descripcio !== null && mb_strlen($descripcio) > 1000) {
        $errors[] = ValidacioErrors::massaLlarg('descripcio', 1000);
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
        return;
    }

    try {
        $conn->beginTransaction();

        $sql = "INSERT INTO db_comptabilitat_cataleg_productes
                   (producte, descripcio, actiu, unitat, preu_recomanat)
                VALUES
                   (:producte, :descripcio, :actiu, :unitat, :preu_recomanat)";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':producte', $producte, PDO::PARAM_STR);
        $stmt->bindValue(':descripcio', $descripcio, $descripcio !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':actiu', $actiu, PDO::PARAM_INT);
        $stmt->bindValue(':unitat', $unitat, $unitat !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':preu_recomanat', $preu_recomanat, $preu_recomanat !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

        $stmt->execute();
        $newId = (int)$conn->lastInsertId();

        // Auditoría
        $detalls = sprintf("Creació producte: %s", $producte);
        Audit::registrarCanvi($conn, $userUuid, "INSERT", $detalls, 'db_comptabilitat_cataleg_productes', $newId);

        $conn->commit();

        Response::success(MissatgesAPI::success('create'), ['id' => $newId], 201);
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
