<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;
use App\Config\Audit;
use App\Utils\ValidacioErrors;
use App\Config\DatabaseConnection;

$slug = $routeParams[0];

// Definir el dominio permitido
$allowedOrigin = APP_DOMAIN;

// Llamar a la función para verificar el referer
checkReferer($allowedOrigin);

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
    $trimOrNull = static function ($v): ?string {
        if ($v === null) return null;
        if (is_string($v)) {
            $s = trim($v);
            return ($s === '') ? null : $s;
        }
        $s = trim((string)$v);
        return ($s === '') ? null : $s;
    };
    $toIntOrNull = static function ($v): ?int {
        return (is_numeric($v) && (string)(int)$v === (string)$v) ? (int)$v : (is_numeric($v) ? (int)$v : null);
    };
    $dateOrNull = static function ($v): ?string {
        if (!is_string($v)) return null;
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null; // YYYY-MM-DD
    };
    // Normaliza cantidades: admite "1.234,56" o "1234.56" -> "1234.56"
    $toDecimal = static function ($v): ?string {
        if ($v === null) return null;
        $s = is_string($v) ? trim($v) : trim((string)$v);
        if ($s === '') return null;
        // quita espacios, convierte coma decimal a punto y elimina separadores de miles comunes
        $s = str_replace([' ', "\u{00A0}"], '', $s);
        $s = str_replace(',', '.', $s);
        // opcional: elimina puntos de miles si vienen como "1.234.56" -> trata sólo el primer punto desde la derecha
        // aquí asumimos que ya quedó "1234.56" tras el replace anterior
        if (!preg_match('/^-?\d+(\.\d{1,4})?$/', $s)) return null; // hasta 4 decimales
        return $s;
    };

    // Datos (requeridos)
    $idUser        = $toIntOrNull($data['idUser']        ?? null);
    $facConcepte   = $trimOrNull($data['facConcepte']    ?? null);
    $facData       = $dateOrNull($data['facData']        ?? null);
    $facDueDate    = $dateOrNull($data['facDueDate']     ?? null);
    $facSubtotal   = $toDecimal($data['facSubtotal']    ?? null);
    $facFees       = $toDecimal($data['facFees']        ?? null);
    $facTotal      = $toDecimal($data['facTotal']       ?? null);
    $facVAT        = $toDecimal($data['facVAT']         ?? null);
    $facIva        = $toIntOrNull($data['facIva']        ?? null);
    $facEstat      = $toIntOrNull($data['facEstat']      ?? null);
    $facPaymentType = $toIntOrNull($data['facPaymentType'] ?? null);

    // Validación
    $errors = [];
    if ($idUser === null)           $errors[] = ValidacioErrors::requerit('idUser');
    if ($facConcepte === null)      $errors[] = ValidacioErrors::requerit('facConcepte');
    elseif (mb_strlen($facConcepte) > 255) $errors[] = ValidacioErrors::massaLlarg('facConcepte', 255);

    if ($facData === null)          $errors[] = ValidacioErrors::dataNoValida('facData');
    if ($facDueDate === null)       $errors[] = ValidacioErrors::dataNoValida('facDueDate');

    if ($facSubtotal === null)      $errors[] = ValidacioErrors::requerit('facSubtotal');
    if ($facFees === null)          $errors[] = ValidacioErrors::requerit('facFees');
    if ($facTotal === null)         $errors[] = ValidacioErrors::requerit('facTotal');
    if ($facVAT === null)           $errors[] = ValidacioErrors::requerit('facVAT');

    if ($facIva === null)           $errors[] = ValidacioErrors::requerit('facIva');
    if ($facEstat === null)         $errors[] = ValidacioErrors::requerit('facEstat');
    if ($facPaymentType === null)   $errors[] = ValidacioErrors::requerit('facPaymentType');

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        global $conn, $userUuid;
        /** @var PDO $conn */
        $conn->beginTransaction();

        $sql = "INSERT INTO db_comptabilitat_facturacio_clients
              (idUser, facConcepte, facData, facDueDate, facSubtotal, facFees, facTotal, facVAT, facIva, facEstat, facPaymentType)
            VALUES
              (:idUser, :facConcepte, :facData, :facDueDate, :facSubtotal, :facFees, :facTotal, :facVAT, :facIva, :facEstat, :facPaymentType)";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':idUser',        $idUser,        PDO::PARAM_INT);
        $stmt->bindValue(':facConcepte',   $facConcepte,   PDO::PARAM_STR);
        $stmt->bindValue(':facData',       $facData,       PDO::PARAM_STR);
        $stmt->bindValue(':facDueDate',    $facDueDate,    PDO::PARAM_STR);
        // DECIMAL: bindea como string para no perder precisión
        $stmt->bindValue(':facSubtotal',   $facSubtotal,   PDO::PARAM_STR);
        $stmt->bindValue(':facFees',       $facFees,       PDO::PARAM_STR);
        $stmt->bindValue(':facTotal',      $facTotal,      PDO::PARAM_STR);
        $stmt->bindValue(':facVAT',        $facVAT,        PDO::PARAM_STR);

        $stmt->bindValue(':facIva',        $facIva,        PDO::PARAM_INT);
        $stmt->bindValue(':facEstat',      $facEstat,      PDO::PARAM_INT);
        $stmt->bindValue(':facPaymentType', $facPaymentType, PDO::PARAM_INT);

        $stmt->execute();
        $newId = (int)$conn->lastInsertId();

        // Auditoría
        $detalls = sprintf("Creació factura client=%d concepte=%s data=%s", $idUser, $facConcepte, $facData);
        Audit::registrarCanvi($conn, $userUuid, "INSERT", $detalls, 'db_comptabilitat_facturacio_clients', $newId);

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
