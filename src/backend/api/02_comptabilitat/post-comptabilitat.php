<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Config\Audit;
use App\Utils\ValidacioErrors;
use App\Config\DatabaseConnection;
use App\Config\Database;
use App\Utils\Uuid;
use Ramsey\Uuid\Uuid as ramsey;
use App\Utils\Schema\SchemaProcessor;
use App\Utils\AdminMiddleware;
use App\Services\ClientService;
use App\Modules\Pressupostos\Schema\PressupostSchema;
use App\Modules\Emissors\Schema\EmissorSchema;
use App\Utils\Schema\SchemaValidationException;

/** @var array $routeParams */
/** @var array $conn */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

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
    $yearPrefix = "$year-%";

    $table = qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS, $db);

    $sql = <<<SQL
        SELECT numero_factura
        FROM {$table}
        WHERE numero_factura LIKE :yearPrefix
        AND numero_factura LIKE '%-C'
        ORDER BY id DESC
        LIMIT 1
        SQL;

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':yearPrefix' => $yearPrefix
    ]);

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

    AdminMiddleware::handle();

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(
            message: MissatgesAPI::error('validacio'),
            errors: ['JSON invàlid'],
            httpCode: 400
        );
        return;
    }

    $clientService = new ClientService($db);

    try {

        $result = $clientService->create($data);

        Response::success(
            message: MissatgesAPI::success('create'),
            data: $result,
            httpCode: 201
        );
    } catch (SchemaValidationException $e) {

        Response::error(
            message: MissatgesAPI::error('validacio'),
            errors: $e->toApiArray(),
            httpCode: 400
        );
    } catch (Throwable $e) {

        Response::error(
            message: MissatgesAPI::error('errorBD'),
            errors: [$e->getMessage()],
            httpCode: 500
        );
    }
} else if ($slug === 'facturaClient') {
    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    if (!is_array($data)) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['JSON invàlid'],
            400
        );
        return;
    }


    // Normalizar
    $emissor_id     = Normalizer::int($data['client_id'] ?? null);
    $client_id      = Normalizer::int($data['client_id'] ?? null);
    $tipus_iva      = Normalizer::int($data['tipus_iva'] ?? null);
    $estat      = Normalizer::int($data['estat'] ?? null);
    $metode_pagament      = Normalizer::int($data['metode_pagament'] ?? null);
    $projecte_id      = Normalizer::int($data['projecte_id'] ?? null);

    $clientNom      = Normalizer::string($data['clientNom'] ?? null);
    $concepte      = Normalizer::string($data['concepte'] ?? null);
    $notes      = Normalizer::string($data['notes'] ?? null);
    $arxiu_url      = Normalizer::string($data['arxiu_url'] ?? null);

    $data_factura   = Normalizer::date($data['data_factura'] ?? null);
    $data_venciment   = Normalizer::date($data['data_venciment'] ?? null);

    $clientEmail    = Normalizer::email($data['clientEmail'] ?? null);

    $total_factura  = Normalizer::decimal($data['total_factura'] ?? null);
    $base_imposable  = Normalizer::decimal($data['base_imposable'] ?? null);
    $despeses_extra  = Normalizer::decimal($data['despeses_extra'] ?? null);
    $import_iva  = Normalizer::decimal($data['import_iva'] ?? null);

    $recurrent   = Normalizer::int($data['recurrent'] ?? null);
    $frequencia  = Normalizer::string($data['frequencia'] ?? null);

    if (!$recurrent) {
        $frequencia = null;
    }

    $detallsProductes = $data['productes'] ?? [];

    // Validación
    $errors = [];

    Validator::required($errors, 'emissor_id', $emissor_id);
    Validator::required($errors, 'client_id', $client_id);
    Validator::required($errors, 'concepte', $concepte);

    Validator::date($errors, 'data_factura', $data_factura);
    Validator::date($errors, 'data_venciment', $data_venciment);

    Validator::required($errors, 'base_imposable', $base_imposable);
    Validator::required($errors, 'total_factura', $total_factura);
    Validator::required($errors, 'import_iva', $import_iva);
    Validator::required($errors, 'tipus_iva', $tipus_iva);
    Validator::required($errors, 'estat', $estat);
    Validator::required($errors, 'metode_pagament', $metode_pagament);


    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        global $conn, $userUuid;
        $conn->beginTransaction();

        $numero_factura = generarNumeroFactura($conn);

        // Inserta factura
        $table = qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS, $pdo);

        $sql = <<<SQL
                INSERT INTO {$table}
                (numero_factura, emissor_id, client_id, concepte, data_factura, data_venciment,
                base_imposable, despeses_extra, total_factura, import_iva, tipus_iva, estat,
                metode_pagament, notes, projecte_id, arxiu_url, recurrent, frequencia)
                VALUES
                (:numero_factura, :emissor_id, :client_id, :concepte, :data_factura, :data_venciment,
                :base_imposable, :despeses_extra, :total_factura, :import_iva, :tipus_iva, :estat,
                :metode_pagament, :notes, :projecte_id, :arxiu_url, :recurrent, :frequencia)
                SQL;

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
            ':frequencia' => $frequencia,
        ]);
        $newId = (int)$conn->lastInsertId();

        // Inserta productos
        if (!empty($detallsProductes)) {

            $table = qi(Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS_PRODUCTES, $pdo);

            $sqlProd = <<<SQL
                INSERT INTO {$table}
                (factura_id, producte_id, descripcio, preu)
                VALUES
                (:factura_id, :producte_id, :descripcio, :preu)
                SQL;

            $stmtProd = $conn->prepare($sqlProd);

            foreach ($detallsProductes as $p) {
                $stmtProd->execute([
                    ':factura_id' => $numero_factura,
                    ':producte_id' => $p['producte_id'] ?? null,
                    ':descripcio' => $p['descripcio'] ?? null,
                    ':preu' => $p['preu'] ?? null
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
        $schema = EmissorSchema::create();

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
    $id        = ramsey::uuid7()->getBytes();

    $pais_id   = $emissorData['pais_id'] ?? null;
    $pais_id   = $pais_id ? Uuid::toBinary($pais_id) : null;

    // -------------------------
    // INSERT
    // -------------------------
    try {

        $conn->beginTransaction();

        $table = qi(Tables::DB_COMPTABILITAT_EMISSORS, $pdo);

        $sql = <<<SQL
        INSERT INTO {$table}
        (id, nom, nif, numero_iva, pais_id, adreca, telefon, email, created_at, updated_at)
        VALUES
        (:id, :nom, :nif, :numero_iva, :pais_id, :adreca, :telefon, :email, NOW(), NOW())
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

        $stmt->bindValue(':pais_id', $pais_id, $pais_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

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
            "Creació emissor: %s (%s)",
            $emissorData['nom'],
            $emissorData['email'] ?? '-'
        );

        Audit::registrarCanvi(
            $conn,
            $userUuid,
            "INSERT",
            $detalls,
            'db_comptabilitat_emissors',
            $id
        );

        $conn->commit();

        Response::success(
            MissatgesAPI::success('create'),
            ['id' => $id],
            201
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

    // POST : Crear nou producte
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

        $table = qi(Tables::DB_COMPTABILITAT_CATALEG_PRODUCTES, $pdo);
        $sql = <<<SQL
                INSERT INTO {$table}
                  (producte, descripcio, actiu, unitat, preu_recomanat)
                VALUES
                  (:producte, :descripcio, :actiu, :unitat, :preu_recomanat)
                SQL;

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
} else if ($slug === 'proveidor') {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    // Helpers
    $trimOrNull  = static fn($v): ?string => (is_string($v) && trim($v) !== '') ? trim($v) : null;

    // Datos
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
                INSERT INTO {$table}
                    (nom, nif, adreca, ciutat, codi_postal, pais, telefon, email, web, contacte, notes)
                VALUES
                  (:nom, :nif, :adreca, :ciutat, :codi_postal, :pais, :telefon, :email, :web, :contacte, :notes)
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

        $stmt->execute();
        $newId = (int)$conn->lastInsertId();

        // Auditoría
        $detalls = sprintf("Creació proveïdor: %s (%s)", $nom, $email ?? '-');
        Audit::registrarCanvi($conn, $userUuid, "INSERT", $detalls, 'db_comptabilitat_proveidors', $newId);

        $conn->commit();

        Response::success(MissatgesAPI::success('create'), ['id' => $newId], 201);
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    // POST : Crear nova factura de despesa
    // ruta => "/api/comptabilitat/post/despesa"
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

    // Validación
    $errors = [];
    if ($data_factura === null) {
        $errors[] = ValidacioErrors::requerit('data');
    }
    if ($concepte === null) {
        $errors[] = ValidacioErrors::requerit('concepte');
    } elseif (mb_strlen($concepte) > 255) {
        $errors[] = ValidacioErrors::massaLlarg('concepte', 255);
    }
    if ($base_imposable === null) {
        $errors[] = ValidacioErrors::requerit('base_imposable');
    }
    if ($total === null) {
        $errors[] = ValidacioErrors::requerit('total');
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
        return;
    }

    try {
        $conn->beginTransaction();

        $table = qi(Tables::DB_COMPTABILITAT_DESPESES, $pdo);
        $sql = <<<SQL
                INSERT INTO {$table}
                    (data, data_pagament, concepte, proveidor_id, receptor_id, base_imposable, tipus_iva, import_iva, total, 
                 metode_pagament, pagat, categoria_id, subcategoria_id, tipus_despesa, client_id, projecte_id, arxiu_url, 
                 deduible, recurrent, frequencia, notes)
                VALUES
                   (:data, :data_pagament, :concepte, :proveidor_id, :receptor_id, :base_imposable, :tipus_iva, :import_iva, :total,
                 :metode_pagament, :pagat, :categoria_id, :subcategoria_id, :tipus_despesa, :client_id, :projecte_id, :arxiu_url,
                 :deduible, :recurrent, :frequencia, :notes)
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

        $stmt->execute();
        $newId = (int)$conn->lastInsertId();

        // Auditoría
        $detalls = sprintf("Creació despesa: %s (%s)", $concepte, $proveidor_id);
        Audit::registrarCanvi($conn, $userUuid, "INSERT", $detalls, 'db_comptabilitat_despeses', $newId);

        $conn->commit();

        Response::success(MissatgesAPI::success('create'), ['id' => $newId], 201);
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

    // Datos normalizados y validados
    try {
        $schema = PressupostSchema::create();
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

    // UUID + BINARIS
    $id = ramsey::uuid7()->getBytes();

    $client_id  = isset($pressupostData['client_id']) ? Uuid::toBinary($pressupostData['client_id']) : null;
    $servei_id  = isset($pressupostData['servei_id']) ? Uuid::toBinary($pressupostData['servei_id']) : null;
    $estat_id   = isset($pressupostData['estat_id']) ? Uuid::toBinary($pressupostData['estat_id']) : null;

    try {

        $conn->beginTransaction();

        $table = qi(Tables::DB_COMPTABILITAT_PRESSUPOSTOS, $pdo);

        $sql = <<<SQL
        INSERT INTO {$table}
        (id, concepte, client_id, servei_id, estat_id, import, data)
        VALUES
        (:id, :concepte, :client_id, :servei_id, :estat_id, :import, :data)
    SQL;

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_LOB);
        $stmt->bindValue(':concepte', $pressupostData['concepte'], PDO::PARAM_STR);

        $stmt->bindValue(':client_id', $client_id, PDO::PARAM_STR);
        $stmt->bindValue(':servei_id', $servei_id, PDO::PARAM_STR);
        $stmt->bindValue(':estat_id', $estat_id, PDO::PARAM_STR);

        $stmt->bindValue(':import', $pressupostData['import'], PDO::PARAM_STR);
        $stmt->bindValue(':data', $pressupostData['data'], PDO::PARAM_STR);

        $stmt->execute();

        // AUDITORIA
        $detalls = sprintf(
            "Creació pressupost: %s (import: %s)",
            $pressupostData['concepte'],
            $pressupostData['import']
        );

        Audit::registrarCanvi(
            $conn,
            $userUuid,
            "INSERT",
            $detalls,
            'db_comptabilitat_pressupostos',
            $id
        );

        $conn->commit();

        Response::success(
            MissatgesAPI::success('create'),
            ['id' => $id],
            201
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
