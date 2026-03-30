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
    $trimOrNull  = static fn($v): ?string => (is_string($v) && trim($v) !== '') ? trim($v) : null;
    $toIntOrNull = static fn($v): ?int    => (is_numeric($v) ? (int)$v : null);
    $uuidOrNull  = static function ($v): ?string {
        if ($v === null || $v === '') return null;
        $s = is_string($v) ? trim($v) : '';
        if ($s === '' || preg_match('/^0{8}-0{4}-0{4}-0{4}-0{12}$/i', $s)) return null;
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $s) ? strtolower($s) : null;
    };
    $dateOrNull  = static fn($v): ?string => (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) ? $v : null;

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
    $trimOrNull = static function ($v): ?string {
        if ($v === null) return null;
        $s = is_string($v) ? trim($v) : trim((string)$v);
        return $s === '' ? null : $s;
    };
    $toIntOrNull = static function ($v): ?int {
        return (is_numeric($v) && (string)(int)$v === (string)$v) ? (int)$v : (is_numeric($v) ? (int)$v : null);
    };
    $dateOrNull = static function ($v): ?string {
        return is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null;
    };
    $toDecimal = static function ($v): ?string {
        if ($v === null) return null;
        $s = is_string($v) ? trim($v) : trim((string)$v);
        $s = str_replace([' ', "\u{00A0}"], '', $s);
        $s = str_replace(',', '.', $s);
        return preg_match('/^-?\d+(\.\d{1,4})?$/', $s) ? $s : null;
    };

    // Datos requeridos
    $idFactura       = $toIntOrNull($data['idFactura'] ?? null);
    $emissorId       = $toIntOrNull($data['emissor_id'] ?? null);
    $clientId        = $toIntOrNull($data['clientId'] ?? null);
    $concepte        = $trimOrNull($data['concepte'] ?? null);
    $dataFactura     = $dateOrNull($data['dataFactura'] ?? null);
    $dataVenciment   = $dateOrNull($data['dataVenciment'] ?? null);
    $baseImposable   = $toDecimal($data['baseImposable'] ?? null);
    $despesesExtra   = $toDecimal($data['despesesExtra'] ?? 0);
    $totalFactura    = $toDecimal($data['totalFactura'] ?? null);
    $importIva       = $toDecimal($data['importIva'] ?? null);
    $tipusIva        = $toIntOrNull($data['tipusIva'] ?? null);
    $estat           = $toIntOrNull($data['estat'] ?? null);
    $metodePagament  = $toIntOrNull($data['metodePagament'] ?? null);
    $notes           = $trimOrNull($data['notes'] ?? null);
    $projecteId      = $toIntOrNull($data['projecteId'] ?? null);
    $arxiuUrl        = $trimOrNull($data['arxiuUrl'] ?? null);
    $detallsProductes = $data['productes'] ?? [];

    // Validación
    $errors = [];
    if ($idFactura === null) $errors[] = ValidacioErrors::requerit('idFactura');
    if ($emissorId === null) $errors[] = ValidacioErrors::requerit('emissor_id');
    if ($clientId === null)  $errors[] = ValidacioErrors::requerit('clientId');
    if ($concepte === null)  $errors[] = ValidacioErrors::requerit('concepte');
    if ($dataFactura === null) $errors[] = ValidacioErrors::dataNoValida('dataFactura');
    if ($dataVenciment === null) $errors[] = ValidacioErrors::dataNoValida('dataVenciment');
    if ($baseImposable === null) $errors[] = ValidacioErrors::requerit('baseImposable');
    if ($totalFactura === null) $errors[] = ValidacioErrors::requerit('totalFactura');
    if ($importIva === null) $errors[] = ValidacioErrors::requerit('importIva');
    if ($tipusIva === null) $errors[] = ValidacioErrors::requerit('tipusIva');
    if ($estat === null) $errors[] = ValidacioErrors::requerit('estat');
    if ($metodePagament === null) $errors[] = ValidacioErrors::requerit('metodePagament');

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        global $conn, $userUuid;
        $conn->beginTransaction();

        // Actualiza factura
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
                    arxiu_url = :arxiu_url
                WHERE id = :idFactura";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':idFactura', $idFactura, PDO::PARAM_INT);
        $stmt->bindValue(':emissor_id', $emissorId, PDO::PARAM_INT);
        $stmt->bindValue(':client_id', $clientId, PDO::PARAM_INT);
        $stmt->bindValue(':concepte', $concepte, PDO::PARAM_STR);
        $stmt->bindValue(':data_factura', $dataFactura, PDO::PARAM_STR);
        $stmt->bindValue(':data_venciment', $dataVenciment, PDO::PARAM_STR);
        $stmt->bindValue(':base_imposable', $baseImposable, PDO::PARAM_STR);
        $stmt->bindValue(':despeses_extra', $despesesExtra, PDO::PARAM_STR);
        $stmt->bindValue(':total_factura', $totalFactura, PDO::PARAM_STR);
        $stmt->bindValue(':import_iva', $importIva, PDO::PARAM_STR);
        $stmt->bindValue(':tipus_iva', $tipusIva, PDO::PARAM_INT);
        $stmt->bindValue(':estat', $estat, PDO::PARAM_INT);
        $stmt->bindValue(':metode_pagament', $metodePagament, PDO::PARAM_INT);
        $stmt->bindValue(':notes', $notes, PDO::PARAM_STR);
        $stmt->bindValue(':projecte_id', $projecteId, PDO::PARAM_INT);
        $stmt->bindValue(':arxiu_url', $arxiuUrl, PDO::PARAM_STR);
        $stmt->execute();

        // Productos: eliminar antiguos y añadir nuevos
        $conn->prepare("DELETE FROM db_comptabilitat_facturacio_clients_productes WHERE factura_id = :idFactura")
            ->execute([':idFactura' => $idFactura]);

        if (!empty($detallsProductes)) {
            $sqlProd = "INSERT INTO db_comptabilitat_facturacio_clients_productes
                        (factura_id, producte_id, notes, preu)
                        VALUES (:factura_id, :producte_id, :notes, :preu)";
            $stmtProd = $conn->prepare($sqlProd);

            foreach ($detallsProductes as $p) {
                $stmtProd->bindValue(':factura_id', $idFactura, PDO::PARAM_INT);
                $stmtProd->bindValue(':producte_id', $toIntOrNull($p['producte_id'] ?? null), PDO::PARAM_INT);
                $stmtProd->bindValue(':notes', $trimOrNull($p['notes'] ?? null), PDO::PARAM_STR);
                $stmtProd->bindValue(':preu', $toDecimal($p['preu'] ?? null), PDO::PARAM_STR);
                $stmtProd->execute();
            }
        }

        // Auditoría
        $detalls = sprintf("Actualització factura client=%d concepte=%s data=%s", $clientId, $concepte, $dataFactura);
        Audit::registrarCanvi($conn, $userUuid, "UPDATE", $detalls, 'db_comptabilitat_facturacio_clients', $idFactura);

        $conn->commit();
        Response::success(MissatgesAPI::success('update'), ['id' => $idFactura], 200);
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
