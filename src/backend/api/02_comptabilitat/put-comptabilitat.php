<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Config\Audit;
use App\Utils\ValidacioErrors;
use App\Config\Database;
use App\Config\DatabaseConnection;
use App\Utils\Uuid;
use App\Utils\Schema\SchemaProcessor;
use App\Modules\Clients\Schema\ClientSchema;
use App\Modules\Pressupostos\Schema\PressupostSchema;
use App\Modules\Emissors\Schema\EmissorSchema;
use App\Utils\Schema\SchemaValidationException;

/** @var array $routeParams */
/** @var array $conn */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

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
        Response::error(
            MissatgesAPI::error('validacio'),
            ['JSON invàlid'],
            400
        );
        return;
    }

    // Datos normalizados y validados
    try {
        $schema = ClientSchema::update();
        $clientData = SchemaProcessor::process(
            $data,
            $schema
        );
    } catch (SchemaValidationException $e) {

        Response::error(
            MissatgesAPI::error('validacio'),
            $e->toApiArray(),
            400
        );
        return;
    }

    try {
        global $conn, $userUuid;
        /** @var PDO $conn */
        $conn->beginTransaction();

        // recuperar variables ja normalitzades
        $id = $clientData['id'];
        $ciutat_id = $clientData['ciutat_id'];
        $provincia_id = $clientData['provincia_id'];
        $pais_id = $clientData['pais_id'];
        $estat_id = $clientData['estat_id'];

        $clientNom = $clientData['clientNom'];
        $clientCognoms = $clientData['clientCognoms'];
        $clientEmail = $clientData['clientEmail'];
        $clientWeb = $clientData['clientWeb'];
        $clientNIF = $clientData['clientNIF'];
        $clientEmpresa = $clientData['clientEmpresa'];
        $clientAdreca = $clientData['clientAdreca'];
        $clientCP = $clientData['clientCP'];
        $clientTelefon = $clientData['clientTelefon'];
        $clientRegistre = $clientData['clientRegistre'];

        // convertir uuid a binary16
        $id_bin = uuid::toBinary($id);
        $ciutat_id_bin = !empty($ciutat_id) ? uuid::toBinary($ciutat_id) : null;
        $provincia_id_bin = !empty($provincia_id) ? uuid::toBinary($provincia_id) : null;
        $pais_id_bin = !empty($pais_id) ? uuid::toBinary($pais_id) : null;
        $estat_id_bin = !empty($estat_id) ? uuid::toBinary($estat_id) : null;

        // Comprobar existencia
        $chk = $conn->prepare("SELECT 1 FROM db_comptabilitat_clients WHERE id = :id");
        $chk->bindValue(':id', $id_bin, PDO::PARAM_LOB);
        $chk->execute();
        if (!$chk->fetchColumn()) {
            $conn->rollBack();
            Response::error(MissatgesAPI::error('not_found'), ["Client id {$id} no existeix"], 404);
            return;
        }

        $table = qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo);
        $sql = <<<SQL
                UPDATE {$table}
                    SET clientNom = :clientNom,
                       clientCognoms = :clientCognoms,
                       clientEmail = :clientEmail,
                       clientWeb = :clientWeb,
                       clientNIF = :clientNIF,
                       clientEmpresa = :clientEmpresa,
                       clientAdreca = :clientAdreca,
                       clientCP = :clientCP,
                       ciutat_id = :ciutat_id,
                       provincia_id = :provincia_id,
                       pais_id = :pais_id,
                       clientTelefon = :clientTelefon,
                       estat_id = :estat_id,
                       clientRegistre = :clientRegistre
                    WHERE id = :id
                SQL;

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':clientNom', $clientNom, PDO::PARAM_STR);
        $stmt->bindValue(':clientCognoms', $clientCognoms, $clientCognoms !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':clientEmail', $clientEmail, $clientEmail !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':clientWeb', $clientWeb, $clientWeb !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':clientNIF', $clientNIF, $clientNIF !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':clientEmpresa', $clientEmpresa, $clientEmpresa !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':clientAdreca', $clientAdreca, $clientAdreca !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':clientCP', $clientCP, $clientCP !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

        $stmt->bindValue(':ciutat_id', $ciutat_id_bin, $ciutat_id_bin !== null ? PDO::PARAM_LOB : PDO::PARAM_NULL);
        $stmt->bindValue(':provincia_id', $provincia_id_bin, $provincia_id_bin !== null ? PDO::PARAM_LOB : PDO::PARAM_NULL);
        $stmt->bindValue(':pais_id', $pais_id_bin, $pais_id_bin !== null ? PDO::PARAM_LOB : PDO::PARAM_NULL);
        $stmt->bindValue(':estat_id', $estat_id_bin, $estat_id_bin !== null ? PDO::PARAM_LOB : PDO::PARAM_NULL);
        $stmt->bindValue(':id', $id_bin, PDO::PARAM_LOB);

        $stmt->bindValue(':clientTelefon', $clientTelefon, $clientTelefon !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':clientRegistre', $clientRegistre, $clientRegistre !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

        $stmt->execute();

        // Auditoría
        $detalls = sprintf("Actualització client: %s (%s)", $clientNom, $clientEmail ?? '-');
        Audit::registrarCanvi($conn, $userUuid, "UPDATE", $detalls, 'db_comptabilitat_clients', $id);

        $conn->commit();

        Response::success(MissatgesAPI::success('update'), ['id' => $id], 200);
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
        return;
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
        $table = qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS, $pdo);
        $sql = <<<SQL
                UPDATE {$table}
                    SET clientNom = :clientNom,
                       clientCognoms = :clientCognoms,
                       clientEmail = :clientEmail,
                       clientWeb = :clientWeb,
                       clientNIF = :clientNIF,
                       clientEmpresa = :clientEmpresa,
                       clientAdreca = :clientAdreca,
                       clientCP = :clientCP,
                       ciutat_id = :ciutat_id,
                       provincia_id = :provincia_id,
                       pais_id = :pais_id,
                       clientTelefon = :clientTelefon,
                       clientStatus = :clientStatus,
                       clientRegistre = :clientRegistre
                    WHERE id = :id
                SQL;

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
            ->execute([':id' => $numero_factura]);

        if (!empty($productes)) {
            $sqlProd = "INSERT INTO db_comptabilitat_facturacio_clients_productes
                        (factura_id, producte_id, descripcio, preu)
                        VALUES (:factura_id, :producte_id, :descripcio, :preu)";
            $stmtProd = $conn->prepare($sqlProd);

            foreach ($productes as $p) {
                $stmtProd->execute([
                    ':factura_id' => $numero_factura,
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
} else if ($slug === 'proveidor') {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    // Helpers
    $trimOrNull  = static fn($v): ?string => (is_string($v) && trim($v) !== '') ? trim($v) : null;
    $toIntOrNull = static fn($v): ?int    => (is_numeric($v) ? (int)$v : null);

    // Datos
    $id         = $toIntOrNull($data['id'] ?? null);
    $nom        = $trimOrNull($data['nom'] ?? null);
    $nif        = $trimOrNull($data['nif'] ?? null);
    $adreca     = $trimOrNull($data['adreca'] ?? null);
    $ciutat     = $trimOrNull($data['ciutat'] ?? null);
    $codi_postal = $trimOrNull($data['codi_postal'] ?? null);
    $pais       = $trimOrNull($data['pais'] ?? null);
    $telefon    = $trimOrNull($data['telefon'] ?? null);
    $email      = $trimOrNull($data['email'] ?? null);
    $web        = $trimOrNull($data['web'] ?? null);
    $contacte   = $trimOrNull($data['contacte'] ?? null);
    $notes      = $trimOrNull($data['notes'] ?? null);

    // Validación
    $errors = [];
    if (!$id) {
        $errors[] = ValidacioErrors::requerit('id');
    }
    if ($nom === null) {
        $errors[] = ValidacioErrors::requerit('nom');
    } elseif (mb_strlen($nom) > 255) {
        $errors[] = ValidacioErrors::massaLlarg('nom', 255);
    }
    if ($nif !== null && mb_strlen($nif) > 20) {
        $errors[] = ValidacioErrors::massaLlarg('nif', 20);
    }
    if ($codi_postal !== null && mb_strlen($codi_postal) > 20) {
        $errors[] = ValidacioErrors::massaLlarg('codi_postal', 20);
    }
    if ($ciutat !== null && mb_strlen($ciutat) > 100) {
        $errors[] = ValidacioErrors::massaLlarg('ciutat', 100);
    }
    if ($pais !== null && mb_strlen($pais) > 50) {
        $errors[] = ValidacioErrors::massaLlarg('pais', 50);
    }
    if ($telefon !== null && mb_strlen($telefon) > 30) {
        $errors[] = ValidacioErrors::massaLlarg('telefon', 30);
    }
    if ($email !== null && mb_strlen($email) > 100) {
        $errors[] = ValidacioErrors::massaLlarg('email', 100);
    }
    if ($web !== null && mb_strlen($web) > 100) {
        $errors[] = ValidacioErrors::massaLlarg('web', 100);
    }
    if ($contacte !== null && mb_strlen($contacte) > 100) {
        $errors[] = ValidacioErrors::massaLlarg('contacte', 100);
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        $conn->beginTransaction();

        $table = qi(Tables::DB_COMPTABILITAT_PROVEIDORS, $pdo);
        $sql = <<<SQL
                UPDATE {$table}
                    SET 
                        nom = :nom,
                        nif = :nif,
                        adreca = :adreca,
                        ciutat = :ciutat,
                        codi_postal = :codi_postal,
                        pais = :pais,
                        telefon = :telefon,
                        email = :email,
                        web = :web,
                        contacte = :contacte,
                        notes = :notes,
                        updated_at = CURRENT_TIMESTAMP()
                    WHERE id = :id
                SQL;

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindValue(':nif', $nif ?? null, $nif !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':adreca', $adreca ?? null, $adreca !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':ciutat', $ciutat ?? null, $ciutat !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':codi_postal', $codi_postal ?? null, $codi_postal !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':pais', $pais ?? null, $pais !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':telefon', $telefon ?? null, $telefon !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':email', $email ?? null, $email !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':web', $web ?? null, $web !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':contacte', $contacte ?? null, $contacte !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':notes', $notes ?? null, $notes !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        // Auditoría
        $detalls = sprintf("Modificació proveïdor: %s (%s)", $nom, $email ?? '-');
        Audit::registrarCanvi($conn, $userUuid, "UPDATE", $detalls, 'db_comptabilitat_proveidors', $id);

        $conn->commit();

        Response::success(MissatgesAPI::success('update'), ['id' => $id], 200);
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // PUT : Actualitzar una factura de despesa
    // ruta => "/api/comptabilitat/put/despesa"
} else if ($slug === "despesa") {

    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
        return;
    }

    // Helpers
    $trimOrNull  = static fn($v): ?string => (is_string($v) && trim($v) !== '') ? trim($v) : null;
    $toFloatOrNull = static fn($v): ?float => is_numeric($v) ? (float)$v : null;
    $toIntOrNull   = static fn($v): ?int => is_numeric($v) ? (int)$v : null;
    $dateOrNull    = static fn($v): ?string => (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) ? $v : null;

    // ID obligatorio
    $id = $toIntOrNull($data['id'] ?? null);
    if (!$id) {
        Response::error(MissatgesAPI::error('missing_id'), [], 400);
        return;
    }

    // Datos
    $data_factura      = $dateOrNull($data['data'] ?? null);
    $data_pagament     = $dateOrNull($data['data_pagament'] ?? null);
    $concepte          = $trimOrNull($data['concepte'] ?? null);
    $proveidor_id      = $toIntOrNull($data['proveidor_id'] ?? null);
    $receptor_id       = $toIntOrNull($data['receptor_id'] ?? 0);
    $base_imposable    = $toFloatOrNull($data['base_imposable'] ?? null);
    $tipus_iva         = $toFloatOrNull($data['tipus_iva'] ?? 0);
    $import_iva        = $toFloatOrNull($data['import_iva'] ?? 0);
    $total             = $toFloatOrNull($data['total'] ?? null);
    $metode_pagament   = $trimOrNull($data['metode_pagament'] ?? 'transferencia');
    $pagat             = $toIntOrNull($data['pagat'] ?? 0);
    $categoria_id      = $toIntOrNull($data['categoria_id'] ?? null);
    $subcategoria_id   = $toIntOrNull($data['subcategoria_id'] ?? null);
    $tipus_despesa     = $trimOrNull($data['tipus_despesa'] ?? 'professional');
    $client_id         = $toIntOrNull($data['client_id'] ?? null);
    $projecte_id       = $toIntOrNull($data['projecte_id'] ?? null);
    $arxiu_url         = $trimOrNull($data['arxiu_url'] ?? null);
    $deduible          = $toIntOrNull($data['deduible'] ?? 1);
    $recurrent         = $toIntOrNull($data['recurrent'] ?? 0);
    $frequencia        = $trimOrNull($data['frequencia'] ?? null);
    $notes             = $trimOrNull($data['notes'] ?? null);

    // Validación mínima
    $errors = [];
    if ($data_factura === null) $errors[] = ValidacioErrors::requerit('data');
    if ($concepte === null) $errors[] = ValidacioErrors::requerit('concepte');
    if ($base_imposable === null) $errors[] = ValidacioErrors::requerit('base_imposable');
    if ($total === null) $errors[] = ValidacioErrors::requerit('total');

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
        return;
    }

    try {
        $conn->beginTransaction();

        // Recuperamos estado previo para auditoría
        $prev = $conn->prepare("SELECT * FROM db_comptabilitat_despeses WHERE id = :id LIMIT 1");
        $prev->execute([':id' => $id]);
        $prevData = $prev->fetch(PDO::FETCH_ASSOC);
        if (!$prevData) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        // UPDATE
        $table = qi(Tables::DB_COMPTABILITAT_DESPESES, $pdo);
        $sql = <<<SQL
                UPDATE {$table}
                    SET 
                        data = :data,
                        data_pagament = :data_pagament,
                        concepte = :concepte,
                        proveidor_id = :proveidor_id,
                        receptor_id = :receptor_id,
                        base_imposable = :base_imposable,
                        tipus_iva = :tipus_iva,
                        import_iva = :import_iva,
                        total = :total,
                        metode_pagament = :metode_pagament,
                        pagat = :pagat,
                        categoria_id = :categoria_id,
                        subcategoria_id = :subcategoria_id,
                        tipus_despesa = :tipus_despesa,
                        client_id = :client_id,
                        projecte_id = :projecte_id,
                        arxiu_url = :arxiu_url,
                        deduible = :deduible,
                        recurrent = :recurrent,
                        frequencia = :frequencia,
                        notes = :notes,
                        updated_at = CURRENT_TIMESTAMP()
                    WHERE id = :id
                SQL;

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':data', $data_factura, PDO::PARAM_STR);
        $stmt->bindValue(':data_pagament', $data_pagament ?? null, $data_pagament !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':concepte', $concepte, PDO::PARAM_STR);
        $stmt->bindValue(':proveidor_id', $proveidor_id, PDO::PARAM_INT);
        $stmt->bindValue(':receptor_id', $receptor_id, PDO::PARAM_INT);
        $stmt->bindValue(':base_imposable', $base_imposable);
        $stmt->bindValue(':tipus_iva', $tipus_iva);
        $stmt->bindValue(':import_iva', $import_iva);
        $stmt->bindValue(':total', $total);
        $stmt->bindValue(':metode_pagament', $metode_pagament, PDO::PARAM_STR);
        $stmt->bindValue(':pagat', $pagat, PDO::PARAM_INT);
        $stmt->bindValue(':categoria_id', $categoria_id ?? null, $categoria_id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':subcategoria_id', $subcategoria_id ?? null, $subcategoria_id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':tipus_despesa', $tipus_despesa, PDO::PARAM_STR);
        $stmt->bindValue(':client_id', $client_id ?? null, $client_id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':projecte_id', $projecte_id ?? null, $projecte_id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':arxiu_url', $arxiu_url ?? null, $arxiu_url !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':deduible', $deduible, PDO::PARAM_INT);
        $stmt->bindValue(':recurrent', $recurrent, PDO::PARAM_INT);
        $stmt->bindValue(':frequencia', $frequencia ?? null, $frequencia !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':notes', $notes ?? null, $notes !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        // Auditoría
        $detalls = sprintf("Actualització despesa: %s (%s)", $concepte, $proveidor_id);
        Audit::registrarCanvi($conn, $userUuid, "UPDATE", $detalls, 'db_comptabilitat_despeses', $id);

        $conn->commit();

        Response::success(MissatgesAPI::success('update'), ['id' => $id], 200);
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }


    // POST : Crear nou pressupost
    // ruta => "/api/comptabilitat/post/pressupost"
} else if ($slug === "pressupost") {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['JSON invàlid'],
            400
        );
        return;
    }

    // VALIDACIÓ SCHEMA (UPDATE)
    try {
        $schema = PressupostSchema::update();
        $pressupostData = SchemaProcessor::process(
            $data,
            $schema
        );
    } catch (SchemaValidationException $e) {
        Response::error(
            MissatgesAPI::error('validacio'),
            $e->toApiArray(),
            400
        );
        return;
    }

    // UUIDs
    $id = Uuid::toBinary($pressupostData['id']);

    $client_id  = isset($pressupostData['client_id']) ? Uuid::toBinary($pressupostData['client_id']) : null;
    $servei_id  = isset($pressupostData['servei_id']) ? Uuid::toBinary($pressupostData['servei_id']) : null;
    $estat_id   = isset($pressupostData['estat_id']) ? Uuid::toBinary($pressupostData['estat_id']) : null;

    try {

        $conn->beginTransaction();

        $table = qi(Tables::DB_COMPTABILITAT_PRESSUPOSTOS, $pdo);

        // 🔎 comprovar existència
        $check = $pdo->prepare("SELECT id FROM {$table} WHERE id = :id LIMIT 1");
        $check->bindValue(':id', $id, PDO::PARAM_LOB);
        $check->execute();

        if (!$check->fetchColumn()) {
            $conn->rollBack();

            Response::error(
                MissatgesAPI::error('notFound'),
                ['Pressupost no trobat'],
                404
            );
            return;
        }

        // UPDATE
        $sql = <<<SQL
        UPDATE {$table}
        SET
            concepte   = :concepte,
            client_id  = :client_id,
            servei_id  = :servei_id,
            estat_id   = :estat_id,
            import     = :import,
            data       = :data
        WHERE id = :id
    SQL;

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_LOB);
        $stmt->bindValue(':concepte', $pressupostData['concepte'], PDO::PARAM_STR);

        $stmt->bindValue(':client_id', $client_id, PDO::PARAM_STR);
        $stmt->bindValue(':servei_id', $servei_id, PDO::PARAM_STR);
        $stmt->bindValue(':estat_id', $estat_id, PDO::PARAM_STR);

        $stmt->bindValue(':import', (float)$pressupostData['import'], PDO::PARAM_STR);
        $stmt->bindValue(':data', $pressupostData['data'], PDO::PARAM_STR);

        $stmt->execute();

        // AUDITORIA
        $detalls = sprintf(
            "Update pressupost: %s (import: %s)",
            $pressupostData['concepte'],
            $pressupostData['import']
        );

        Audit::registrarCanvi(
            $conn,
            $userUuid,
            "UPDATE",
            $detalls,
            'db_comptabilitat_pressupostos',
            $id
        );

        $conn->commit();

        Response::success(
            MissatgesAPI::success('update'),
            ['id' => $id],
            200
        );
    } catch (Throwable $e) {

        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }
} else if ($slug === 'emissor') {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['JSON invàlid'],
            400
        );
        return;
    }

    // -------------------------
    // SCHEMA VALIDATION
    // -------------------------
    try {
        $schema = EmissorSchema::update();

        $emissorData = SchemaProcessor::process(
            $data,
            $schema
        );
    } catch (SchemaValidationException $e) {

        Response::error(
            MissatgesAPI::error('validacio'),
            $e->toApiArray(),
            400
        );

        return;
    }

    // -------------------------
    // UUID / BINARY CONVERSION
    // -------------------------
    $id = $emissorData['id'];
    $id = Uuid::toBinary($id);

    $pais_id = $emissorData['pais'] ?? null;
    $pais_id = $pais_id ? Uuid::toBinary($pais_id) : null;

    // -------------------------
    // UPDATE
    // -------------------------
    try {

        $conn->beginTransaction();

        $table = qi(Tables::DB_COMPTABILITAT_EMISSORS, $pdo);

        $sql = <<<SQL
        UPDATE {$table}
        SET
            nom = :nom,
            nif = :nif,
            numero_iva = :numero_iva,
            pais_id = :pais_id,
            adreca = :adreca,
            telefon = :telefon,
            email = :email,
            updated_at = NOW()
        WHERE id = :id
    SQL;

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_LOB);

        $stmt->bindValue(':nom', $emissorData['nom'], PDO::PARAM_STR);
        $stmt->bindValue(':nif', $emissorData['nif'], PDO::PARAM_STR);

        $stmt->bindValue(
            ':numero_iva',
            $emissorData['numero_iva'] ?? null,
            $emissorData['numero_iva'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL
        );

        $stmt->bindValue(
            ':pais_id',
            $pais_id,
            $pais_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL
        );

        $stmt->bindValue(
            ':adreca',
            $emissorData['adreca'] ?? null,
            $emissorData['adreca'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL
        );

        $stmt->bindValue(
            ':telefon',
            $emissorData['telefon'] ?? null,
            $emissorData['telefon'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL
        );

        $stmt->bindValue(
            ':email',
            $emissorData['email'] ?? null,
            $emissorData['email'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL
        );

        $stmt->execute();

        // -------------------------
        // AUDITORÍA
        // -------------------------
        $detalls = sprintf(
            "Actualització emissor: %s (%s)",
            $emissorData['nom'],
            $emissorData['email'] ?? '-'
        );

        Audit::registrarCanvi(
            $conn,
            $userUuid,
            "UPDATE",
            $detalls,
            'db_comptabilitat_emissors',
            $id
        );

        $conn->commit();

        Response::success(
            MissatgesAPI::success('update'),
            ['id' => $id],
            200
        );
    } catch (Throwable $e) {

        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

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
