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
        return (is_numeric($v) ? (int)$v : null);
    };
    $dateOrNull = static function ($v): ?string {
        if (!is_string($v)) return null;
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null; // YYYY-MM-DD
    };
    // Normaliza cantidades: "1.234,56" o "1234.56" -> "1234.56"
    $toDecimal = static function ($v): ?string {
        if ($v === null) return null;
        $s = is_string($v) ? trim($v) : trim((string)$v);
        if ($s === '') return null;
        $s = str_replace([' ', "\u{00A0}"], '', $s);
        $s = str_replace(',', '.', $s);
        if (!preg_match('/^-?\d+(\.\d{1,4})?$/', $s)) return null; // ajusta decimales si necesitas
        return $s;
    };

    // Datos (id requerido)
    $id              = $toIntOrNull($data['id'] ?? null);
    $idUser          = $toIntOrNull($data['idUser'] ?? null);
    $facConcepte     = $trimOrNull($data['facConcepte'] ?? null);
    $facData         = $dateOrNull($data['facData'] ?? null);
    $facDueDate      = $dateOrNull($data['facDueDate'] ?? null);
    $facSubtotal     = $toDecimal($data['facSubtotal'] ?? null);
    $facFees         = $toDecimal($data['facFees'] ?? null);
    $facTotal        = $toDecimal($data['facTotal'] ?? null);
    $facVAT          = $toDecimal($data['facVAT'] ?? null);
    $facIva          = $toIntOrNull($data['facIva'] ?? null);
    $facEstat        = $toIntOrNull($data['facEstat'] ?? null);
    $facPaymentType  = $toIntOrNull($data['facPaymentType'] ?? null);

    // Validación
    $errors = [];
    if ($id === null || $id <= 0)      $errors[] = ValidacioErrors::requerit('id');
    if ($idUser === null)              $errors[] = ValidacioErrors::requerit('idUser');

    if ($facConcepte === null)         $errors[] = ValidacioErrors::requerit('facConcepte');
    elseif (mb_strlen($facConcepte) > 255) $errors[] = ValidacioErrors::massaLlarg('facConcepte', 255);

    if ($facData === null)             $errors[] = ValidacioErrors::dataNoValida('facData');
    if ($facDueDate === null)          $errors[] = ValidacioErrors::dataNoValida('facDueDate');

    if ($facSubtotal === null)         $errors[] = ValidacioErrors::requerit('facSubtotal');
    if ($facFees === null)             $errors[] = ValidacioErrors::requerit('facFees');
    if ($facTotal === null)            $errors[] = ValidacioErrors::requerit('facTotal');
    if ($facVAT === null)              $errors[] = ValidacioErrors::requerit('facVAT');

    if ($facIva === null)              $errors[] = ValidacioErrors::requerit('facIva');
    if ($facEstat === null)            $errors[] = ValidacioErrors::requerit('facEstat');
    if ($facPaymentType === null)      $errors[] = ValidacioErrors::requerit('facPaymentType');

    // Coherencia de fechas
    if ($facData !== null && $facDueDate !== null) {
        if (strtotime($facDueDate) < strtotime($facData)) {
            $errors[] = "La data de venciment (facDueDate) no pot ser anterior a la data de factura (facData).";
        }
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        global $conn, $userUuid;
        /** @var PDO $conn */
        $conn->beginTransaction();

        // Comprobar existencia
        $chk = $conn->prepare("SELECT 1 FROM db_comptabilitat_facturacio_clients WHERE id = :id");
        $chk->bindValue(':id', $id, PDO::PARAM_INT);
        $chk->execute();
        if (!$chk->fetchColumn()) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('not_found'), ["Factura id {$id} no existeix"], 404);
        }

        $sql = "UPDATE db_comptabilitat_facturacio_clients
               SET idUser = :idUser,
                   facConcepte = :facConcepte,
                   facData = :facData,
                   facDueDate = :facDueDate,
                   facSubtotal = :facSubtotal,
                   facFees = :facFees,
                   facTotal = :facTotal,
                   facVAT = :facVAT,
                   facIva = :facIva,
                   facEstat = :facEstat,
                   facPaymentType = :facPaymentType
             WHERE id = :id";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':id',            $id,           PDO::PARAM_INT);
        $stmt->bindValue(':idUser',        $idUser,       PDO::PARAM_INT);
        $stmt->bindValue(':facConcepte',   $facConcepte,  PDO::PARAM_STR);
        $stmt->bindValue(':facData',       $facData,      PDO::PARAM_STR);
        $stmt->bindValue(':facDueDate',    $facDueDate,   PDO::PARAM_STR);

        // DECIMAL como string para no perder precisión
        $stmt->bindValue(':facSubtotal',   $facSubtotal,  PDO::PARAM_STR);
        $stmt->bindValue(':facFees',       $facFees,      PDO::PARAM_STR);
        $stmt->bindValue(':facTotal',      $facTotal,     PDO::PARAM_STR);
        $stmt->bindValue(':facVAT',        $facVAT,       PDO::PARAM_STR);

        $stmt->bindValue(':facIva',        $facIva,       PDO::PARAM_INT);
        $stmt->bindValue(':facEstat',      $facEstat,     PDO::PARAM_INT);
        $stmt->bindValue(':facPaymentType', $facPaymentType, PDO::PARAM_INT);

        $stmt->execute();

        // Auditoría
        $detalls = sprintf("Actualització factura id=%d client=%d concepte=%s data=%s", $id, $idUser, $facConcepte, $facData);
        Audit::registrarCanvi($conn, $userUuid, "UPDATE", $detalls, 'db_comptabilitat_facturacio_clients', $id);

        $conn->commit();

        Response::success(MissatgesAPI::success('update'), ['id' => $id], 200);
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }
} else if ($slug === 'detallsFacturaClientProducte') {

    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    // ------- Helpers (mismos criterios que en el POST) -------
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
        if ($v === null) return null;
        if (is_int($v)) return $v;
        if (is_string($v) && preg_match('/^-?\d+$/', $v)) return (int)$v;
        if (is_numeric($v)) return (int)$v;
        return null;
    };
    // Normaliza "1.234,56" o "1234.56" -> "1234.56"
    $toDecimal = static function ($v): ?string {
        if ($v === null) return null;
        $s = is_string($v) ? trim($v) : trim((string)$v);
        if ($s === '') return null;
        $s = str_replace(["\u{00A0}", ' '], '', $s);
        if (strpos($s, '.') !== false && strpos($s, ',') !== false) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            if (strpos($s, ',') !== false && strpos($s, '.') === false) {
                $s = str_replace(',', '.', $s);
            }
        }
        if (!preg_match('/^-?\d+(\.\d{1,4})?$/', $s)) return null;
        return $s;
    };

    // ------- ID de la línea a actualizar -------
    // Prioriza id en ruta (p.ej. /api/invoice-lines/{id}); si no, toma ?id=...
    $id = null;
    if (isset($routeParams[0]) && ctype_digit((string)$routeParams[0])) {
        $id = (int)$routeParams[0];
    } else {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    }
    if (!$id) {
        Response::error(MissatgesAPI::error('validacio'), [ValidacioErrors::requerit('id')], 400);
    }

    // ------- Campos opcionales a actualizar (parciales) -------
    $hasFacturaId  = array_key_exists('factura_id',  $data);
    $hasProducteId = array_key_exists('producte_id', $data);
    $hasNotes      = array_key_exists('notes',       $data);
    $hasPreu       = array_key_exists('preu',        $data);

    if (!$hasFacturaId && !$hasProducteId && !$hasNotes && !$hasPreu) {
        Response::error(MissatgesAPI::error('validacio'), ['No hi ha cap camp per actualitzar'], 400);
    }

    $facturaId  = $hasFacturaId  ? $toIntOrNull($data['factura_id'])   : null;
    $producteId = $hasProducteId ? $toIntOrNull($data['producte_id'])  : null;
    $notesIn    = $hasNotes      ? $data['notes']                      : null;
    $preuIn     = $hasPreu       ? $data['preu']                       : null;

    $notes = $hasNotes ? $trimOrNull($notesIn) : null;

    // preu: si viene clave con vacío -> NULL; si viene con contenido inválido -> error
    $preuStr  = $hasPreu ? $trimOrNull($preuIn) : null;
    $preuNorm = null;
    if ($hasPreu) {
        if ($preuStr !== null) {
            $preuNorm = $toDecimal($preuStr);
            if ($preuNorm === null) {
                Response::error(MissatgesAPI::error('validacio'), [ValidacioErrors::formatNoValid('preu')], 400);
            }
        } // si $preuStr === null => se actualizará a NULL
    }

    // Validación básica de ids
    $errors = [];
    if ($hasFacturaId && $facturaId === null)   $errors[] = ValidacioErrors::formatNoValid('factura_id');
    if ($hasProducteId && $producteId === null) $errors[] = ValidacioErrors::formatNoValid('producte_id');
    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        global $conn, $userUuid;
        /** @var PDO $conn */
        $conn->beginTransaction();

        // 1) Verifica que la línea exista (y bloquea fila)
        $chk = $conn->prepare("SELECT id, factura_id FROM db_comptabilitat_facturacio_clients_productes WHERE id = :id FOR UPDATE");
        $chk->bindValue(':id', $id, PDO::PARAM_INT);
        $chk->execute();
        $row = $chk->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('noTrobat'), ['La línia de factura no existeix'], 404);
        }

        // 2) (Opcional) verificar factura si se cambia
        if ($hasFacturaId && $facturaId !== null) {
            $checkInv = $conn->prepare("SELECT id FROM db_comptabilitat_facturacio_clients WHERE id = :id LIMIT 1");
            $checkInv->bindValue(':id', $facturaId, PDO::PARAM_INT);
            $checkInv->execute();
            if (!$checkInv->fetchColumn()) {
                $conn->rollBack();
                Response::error(MissatgesAPI::error('validacio'), ['La factura indicada no existeix'], 404);
            }
        }

        // 3) Construcción dinámica del UPDATE
        $sets = [];
        $params = [];

        if ($hasFacturaId) {
            $sets[] = 'factura_id = :factura_id';
            if ($facturaId === null) {
                $params[':factura_id'] = [null, PDO::PARAM_NULL];
            } else {
                $params[':factura_id'] = [$facturaId, PDO::PARAM_INT];
            }
        }

        if ($hasProducteId) {
            $sets[] = 'producte_id = :producte_id';
            if ($producteId === null) {
                $params[':producte_id'] = [null, PDO::PARAM_NULL];
            } else {
                $params[':producte_id'] = [$producteId, PDO::PARAM_INT];
            }
        }

        if ($hasNotes) {
            $sets[] = 'notes = :notes';
            if ($notes === null) {
                $params[':notes'] = [null, PDO::PARAM_NULL];
            } else {
                $params[':notes'] = [$notes, PDO::PARAM_STR];
            }
        }

        if ($hasPreu) {
            $sets[] = 'preu = :preu';
            if ($preuStr === null) {
                $params[':preu'] = [null, PDO::PARAM_NULL];
            } else {
                // guardamos el valor normalizado como texto
                $params[':preu'] = [$preuNorm, PDO::PARAM_STR];
            }
        }

        if (empty($sets)) {
            // teóricamente no debería pasar
            $conn->rollBack();
            Response::error(MissatgesAPI::error('validacio'), ['No hi ha cap camp per actualitzar'], 400);
        }

        $sql = "UPDATE db_comptabilitat_facturacio_clients_productes
                   SET " . implode(', ', $sets) . "
                 WHERE id = :id";
        $stmt = $conn->prepare($sql);

        foreach ($params as $k => [$v, $type]) {
            $stmt->bindValue($k, $v, $type);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // 4) Auditoría
        $detalls = sprintf(
            "Actualització línia id=%d%s%s%s%s",
            $id,
            $hasFacturaId  ? " factura_id=" . var_export($facturaId, true)  : "",
            $hasProducteId ? " producte_id=" . var_export($producteId, true) : "",
            $hasNotes      ? " notes=" . var_export($notes, true)          : "",
            $hasPreu       ? " preu=" . var_export($preuStr, true)         : ""
        );
        Audit::registrarCanvi($conn, $userUuid, "UPDATE", $detalls, 'db_comptabilitat_facturacio_clients_productes', $id);

        $conn->commit();
        Response::success(MissatgesAPI::success('update'), ['id' => (int)$id], 200);
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
