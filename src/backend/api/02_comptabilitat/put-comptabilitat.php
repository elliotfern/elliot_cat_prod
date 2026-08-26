<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Config\Audit;
use App\Utils\ValidacioErrors;
use App\Config\Database;
use App\Utils\Uuid;
use App\Utils\Schema\SchemaProcessor;
use App\Modules\Pressupostos\Schema\PressupostSchema;
use App\Modules\Emissors\Schema\EmissorSchema;
use App\Utils\Schema\SchemaValidationException;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

$db = new Database();
$pdo = $db->getPdo();

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

// Check if the request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: PUT");

if ($slug === 'clients') {

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(
            message: 'Dades JSON no vàlides.',
            httpCode: 400
        );
        return;
    }

    // ID obligatori
    if (empty($data['id'])) {
        Response::error(
            message: 'L\'ID del client és obligatori.',
            httpCode: 400
        );
        return;
    }

    // Camps obligatoris
    $requiredFields = [
        'contacte_id',
        'estat_id',
    ];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            Response::error(
                message: "El camp {$field} és obligatori.",
                httpCode: 422
            );
            return;
        }
    }

    try {
        // Comprovar que existeix el client
        $stmt = $pdo->prepare("
            SELECT contacte_id
            FROM db_comptabilitat_clients
            WHERE contacte_id = :contacte_id
            LIMIT 1
        ");

        $stmt->execute([
            'contacte_id' => Uuid::toBinary($data['contacte_id']),
        ]);

        if (!$stmt->fetch()) {
            Response::error(
                message: 'Client no trobat.',
                httpCode: 404
            );
            return;
        }

        $id_bin = Uuid::toBinary($data['id']);
        $contacte_id_bin = Uuid::toBinary($data['contacte_id']);
        $estat_id_bin = Uuid::toBinary($data['estat_id']);

        // Actualitzar
        $sql = "UPDATE db_comptabilitat_clients
            SET
                contacte_id = :contacte_id,
                estat_id = :estat_id
            WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $id_bin, PDO::PARAM_LOB);
        $stmt->bindValue(':contacte_id', $contacte_id_bin, PDO::PARAM_LOB);
        $stmt->bindValue(':estat_id', $estat_id_bin, PDO::PARAM_LOB);

        $stmt->execute();

        Response::success(
            message: MissatgesAPI::success('update'),
            data: [
                'id' => $data['id'],
            ],
            httpCode: 200
        );
    } catch (\PDOException $e) {

        Response::error(
            message: $e->getMessage(),
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

    // ---------------------------------------------------------
    // HELPERS
    // ---------------------------------------------------------

    $trimOrNull = static function ($v): ?string {
        if ($v === null) {
            return null;
        }

        $value = trim((string)$v);

        return $value !== '' ? $value : null;
    };

    $toIntOrNull = static function ($v): ?int {
        return is_numeric($v) ? (int)$v : null;
    };

    // UUID como string
    $uuidOrNull = static function ($v): ?string {
        if ($v === null) {
            return null;
        }

        $value = trim((string)$v);

        return $value !== '' ? $value : null;
    };

    $dateOrNull = static function ($v): ?string {
        if (!is_string($v)) {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)
            ? $v
            : null;
    };

    $toDecimal = static function ($v): ?string {

        if ($v === null || $v === '') {
            return null;
        }

        $value = str_replace(
            [',', ' ', "\u{00A0}"],
            ['.', '', ''],
            (string)$v
        );

        return preg_match('/^-?\d+(\.\d{1,4})?$/', $value)
            ? $value
            : null;
    };


    // ---------------------------------------------------------
    // DATOS FACTURA
    // ---------------------------------------------------------

    $id = $toIntOrNull(
        $data['id'] ?? null
    );

    $numero_factura = $trimOrNull(
        $data['numero_factura'] ?? null
    );

    // UUID
    $emissor_id = $uuidOrNull(
        $data['emissor_id'] ?? null
    );

    // UUID
    $client_id = $uuidOrNull(
        $data['client_id'] ?? null
    );

    $concepte = $trimOrNull(
        $data['concepte'] ?? null
    );

    $data_factura = $dateOrNull(
        $data['data_factura'] ?? null
    );

    $data_venciment = $dateOrNull(
        $data['data_venciment'] ?? null
    );

    $base_imposable = $toDecimal(
        $data['base_imposable'] ?? null
    );

    $despeses_extra = $toDecimal(
        $data['despeses_extra'] ?? 0
    );

    $total_factura = $toDecimal(
        $data['total_factura'] ?? null
    );

    $import_iva = $toDecimal(
        $data['import_iva'] ?? null
    );

    $tipus_iva = $toIntOrNull(
        $data['tipus_iva'] ?? null
    );

    $estat = $toIntOrNull(
        $data['estat'] ?? null
    );

    $metode_pagament = $toIntOrNull(
        $data['metode_pagament'] ?? null
    );

    $notes = $trimOrNull(
        $data['notes'] ?? null
    );

    $projecte_id = $toIntOrNull(
        $data['projecte_id'] ?? null
    );

    $arxiu_url = $trimOrNull(
        $data['arxiu_url'] ?? null
    );

    $recurrent = !empty($data['recurrent']) ? 1 : 0;

    $frequencia = $recurrent
        ? $trimOrNull($data['frequencia'] ?? null)
        : null;

    $productes = $data['productes'] ?? [];


    // ---------------------------------------------------------
    // VALIDACIÓN
    // ---------------------------------------------------------

    $errors = [];

    if ($id === null) {
        $errors[] = ValidacioErrors::requerit('id');
    }

    if ($client_id === null) {
        $errors[] = ValidacioErrors::requerit('client_id');
    }

    if ($data_factura === null) {
        $errors[] = ValidacioErrors::dataNoValida('data_factura');
    }

    if ($data_venciment === null) {
        $errors[] = ValidacioErrors::dataNoValida('data_venciment');
    }

    if ($base_imposable === null) {
        $errors[] = ValidacioErrors::requerit('base_imposable');
    }

    if ($total_factura === null) {
        $errors[] = ValidacioErrors::requerit('total_factura');
    }

    if ($import_iva === null) {
        $errors[] = ValidacioErrors::requerit('import_iva');
    }

    if ($tipus_iva === null) {
        $errors[] = ValidacioErrors::requerit('tipus_iva');
    }

    if ($metode_pagament === null) {
        $errors[] = ValidacioErrors::requerit('metode_pagament');
    }

    if (!is_array($productes)) {
        $errors[] = 'El camp productes ha de ser un array.';
    }

    if (!empty($errors)) {
        Response::error(
            MissatgesAPI::error('validacio'),
            $errors,
            400
        );
        return;
    }


    // ---------------------------------------------------------
    // UPDATE
    // ---------------------------------------------------------

    try {

        $pdo->beginTransaction();


        // -----------------------------------------------------
        // 1. ACTUALIZAR FACTURA
        // -----------------------------------------------------

        $tableFactura = qi(
            Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS,
            $pdo
        );

        $sql = <<<SQL
            UPDATE {$tableFactura}
            SET
                numero_factura = :numero_factura,
                emissor_id = :emissor_id,
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
            WHERE id = :id
        SQL;

        $stmt = $pdo->prepare($sql);

        $emissorIdBinary = $emissor_id !== null
            ? Uuid::toBinary($emissor_id)
            : null;

        $clientIdBinary = $client_id !== null
            ? Uuid::toBinary($client_id)
            : null;

        $stmt->execute([
            ':id'              => $id,
            ':numero_factura'  => $numero_factura,
            ':emissor_id'      => $emissorIdBinary,
            ':client_id'       => $clientIdBinary,
            ':concepte'        => $concepte,
            ':data_factura'    => $data_factura,
            ':data_venciment'  => $data_venciment,
            ':base_imposable'  => $base_imposable,
            ':despeses_extra'  => $despeses_extra,
            ':total_factura'   => $total_factura,
            ':import_iva'      => $import_iva,
            ':tipus_iva'       => $tipus_iva,
            ':estat'           => $estat,
            ':metode_pagament' => $metode_pagament,
            ':notes'           => $notes,
            ':projecte_id'     => $projecte_id,
            ':arxiu_url'       => $arxiu_url,
            ':recurrent'       => $recurrent,
            ':frequencia'      => $frequencia,
        ]);


        // -----------------------------------------------------
        // 2. ELIMINAR PRODUCTOS ACTUALES
        // -----------------------------------------------------

        $tableProductes = qi(
            Tables::DB_COMPTABILITAT_FACTURACIO_CLIENTS_PRODUCTES,
            $pdo
        );

        $sqlDelete = <<<SQL
            DELETE FROM {$tableProductes}
            WHERE factura_id = :factura_id
        SQL;

        $stmtDelete = $pdo->prepare($sqlDelete);

        $stmtDelete->execute([
            ':factura_id' => $id
        ]);


        // -----------------------------------------------------
        // 3. INSERTAR PRODUCTOS NUEVOS
        // -----------------------------------------------------

        if (!empty($productes)) {

            $sqlProducte = <<<SQL
                INSERT INTO {$tableProductes}
                (
                    factura_id,
                    producte_id,
                    descripcio,
                    preu
                )
                VALUES
                (
                    :factura_id,
                    :producte_id,
                    :descripcio,
                    :preu
                )
            SQL;

            $stmtProducte = $pdo->prepare($sqlProducte);

            foreach ($productes as $producte) {

                if (!is_array($producte)) {
                    continue;
                }

                $producte_id = $producte['producte_id'] ?? null;

                $descripcio = $trimOrNull(
                    $producte['descripcio'] ?? null
                );

                $preu = $toDecimal(
                    $producte['preu'] ?? null
                );

                // Si no hay producto seleccionado,
                // no insertamos la fila.
                if ($producte_id === null) {
                    continue;
                }

                $stmtProducte->execute([
                    ':factura_id'  => $id,
                    ':producte_id' => $producte_id,
                    ':descripcio'  => $descripcio,
                    ':preu'        => $preu,
                ]);
            }
        }


        // -----------------------------------------------------
        // 4. AUDITORÍA
        // -----------------------------------------------------

        // -----------------------------------------------------
        // 5. COMMIT
        // -----------------------------------------------------

        $pdo->commit();

        Response::success(
            MissatgesAPI::success('update'),
            ['id' => $id],
            httpCode: 200
        );
    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }
} else if ($slug === 'proveidor') {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('validacio'), ['JSON invàlid'], 400);
    }

    // Datos
    $id = $data['id'];
    $contacte_id = $data['contacte_id'];


    // Validación
    $errors = [];
    if (!$id) {
        $errors[] = ValidacioErrors::requerit('id');
    }

    if ($contacte_id === null) {
        $errors[] = ValidacioErrors::requerit('contacte_id');
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, 400);
    }

    try {
        $pdo->beginTransaction();

        $table = qi(Tables::DB_COMPTABILITAT_PROVEIDORS, $pdo);
        $sql = <<<SQL
                UPDATE {$table}
                    SET 
                        contacte_id = :contacte_id
                    WHERE id = :id
                SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':contacte_id', Uuid::toBinary($contacte_id), PDO::PARAM_LOB);
        $stmt->bindValue(':id', Uuid::toBinary($id), PDO::PARAM_LOB);

        $stmt->execute();
        $pdo->commit();

        Response::success(MissatgesAPI::success('update'), ['id' => $id], httpCode: 200);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
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
    $id = $data['id'];
    if (!$id) {
        Response::error(MissatgesAPI::error('missing_id'), [], 400);
        return;
    }

    // Datos
    $id_bin = Uuid::toBinary($id);
    $proveidor_id_bin = Uuid::toBinary($data['proveidor_id']);
    $receptor_id_bin  = Uuid::toBinary($data['receptor_id']);
    $categoria_id_bin = Uuid::toBinary($data['categoria_id']);
    $subcategoria_id_bin = Uuid::toBinary($data['subcategoria_id']);

    $data_factura      = $dateOrNull($data['data'] ?? null);
    $data_pagament     = $dateOrNull($data['data_pagament'] ?? null);
    $concepte          = $trimOrNull($data['concepte'] ?? null);

    $base_imposable    = $toFloatOrNull($data['base_imposable'] ?? null);
    $tipus_iva         = $toFloatOrNull($data['tipus_iva'] ?? 0);
    $import_iva        = $toFloatOrNull($data['import_iva'] ?? 0);
    $total             = $toFloatOrNull($data['total'] ?? null);
    $metode_pagament   = $trimOrNull($data['metode_pagament'] ?? 'transferencia');
    $pagat             = $toIntOrNull($data['pagat'] ?? 0);

    $tipus_despesa     = $trimOrNull($data['tipus_despesa'] ?? 'professional');
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
        $pdo->beginTransaction();

        // Recuperamos estado previo para auditoría
        $prev = $pdo->prepare("SELECT * FROM db_comptabilitat_despeses WHERE id = :id LIMIT 1");

        $prev->execute([':id' => $id_bin]);

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
                        arxiu_url = :arxiu_url,
                        deduible = :deduible,
                        recurrent = :recurrent,
                        frequencia = :frequencia,
                        notes = :notes,
                        updated_at = CURRENT_TIMESTAMP()
                    WHERE id = :id
                SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id_bin, PDO::PARAM_LOB);
        $stmt->bindValue(':proveidor_id', $proveidor_id_bin, PDO::PARAM_LOB);
        $stmt->bindValue(':receptor_id', $receptor_id_bin, PDO::PARAM_LOB);
        $stmt->bindValue(':categoria_id', $categoria_id_bin, PDO::PARAM_LOB);
        $stmt->bindValue(':subcategoria_id', $subcategoria_id_bin, PDO::PARAM_LOB);

        $stmt->bindValue(':data', $data_factura, PDO::PARAM_STR);
        $stmt->bindValue(':data_pagament', $data_pagament ?? null, $data_pagament !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':concepte', $concepte, PDO::PARAM_STR);
        $stmt->bindValue(':base_imposable', $base_imposable);
        $stmt->bindValue(':tipus_iva', $tipus_iva);
        $stmt->bindValue(':import_iva', $import_iva);
        $stmt->bindValue(':total', $total);
        $stmt->bindValue(':metode_pagament', $metode_pagament, PDO::PARAM_STR);
        $stmt->bindValue(':pagat', $pagat, PDO::PARAM_INT);
        $stmt->bindValue(':tipus_despesa', $tipus_despesa, PDO::PARAM_STR);
        $stmt->bindValue(':arxiu_url', $arxiu_url ?? null, $arxiu_url !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':deduible', $deduible, PDO::PARAM_INT);
        $stmt->bindValue(':recurrent', $recurrent, PDO::PARAM_INT);
        $stmt->bindValue(':frequencia', $frequencia ?? null, $frequencia !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':notes', $notes ?? null, $notes !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

        $stmt->execute();
        $pdo->commit();

        Response::success(MissatgesAPI::success('update'), ['id' => $id], httpCode: 200);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
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

        $pdo->beginTransaction();

        $table = qi(Tables::DB_COMPTABILITAT_PRESSUPOSTOS, $pdo);

        // 🔎 comprovar existència
        $check = $pdo->prepare("SELECT id FROM {$table} WHERE id = :id LIMIT 1");
        $check->bindValue(':id', $id, PDO::PARAM_LOB);
        $check->execute();

        if (!$check->fetchColumn()) {
            $pdo->rollBack();

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

        $pdo->commit();

        Response::success(
            MissatgesAPI::success('update'),
            ['id' => $id],
            httpCode: 200
        );
    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
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

    $pais_id = $emissorData['pais_id'] ?? null;
    $pais_id = $pais_id ? Uuid::toBinary($pais_id) : null;

    // -------------------------
    // UPDATE
    // -------------------------
    try {

        $pdo->beginTransaction();

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
            dataInici = :dataInici,
            dataFi = :dataFi,
            updated_at = NOW()
        WHERE id = :id
    SQL;

        $stmt = $pdo->prepare($sql);

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

        $stmt->bindValue(':dataInici', $emissorData['dataInici'], PDO::PARAM_STR);
        $stmt->bindValue(':dataFi', $emissorData['dataFi'], PDO::PARAM_STR);

        $stmt->execute();

        // -------------------------
        // AUDITORÍA
        // -------------------------
        $detalls = sprintf(
            "Actualització emissor: %s (%s)",
            $emissorData['nom'],
            $emissorData['email'] ?? '-'
        );

        $pdo->commit();

        Response::success(
            MissatgesAPI::success('update'),
            ['id' => $id],
            httpCode: 200
        );
    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }
} else if ($slug === "producte") {
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

    // ID obligatori
    if (empty($data['id'])) {
        Response::error(
            message: 'L\'ID del producte és obligatori.',
            httpCode: 400
        );
        return;
    }

    // Camps obligatoris
    $requiredFields = [
        'id',
        'producte',
    ];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            Response::error(
                message: "El camp {$field} és obligatori.",
                httpCode: 422
            );
            return;
        }
    }

    // UUIDs
    $id_bin = Uuid::toBinary($data['id']);

    $producte  = isset($data['producte']) ? $data['producte'] : null;
    $descripcio  = isset($data['descripcio']) ? $data['descripcio'] : null;
    $unitat   = isset($data['unitat']) ? $data['unitat'] : null;
    $preu_recomanat   = isset($data['preu_recomanat']) ? $data['preu_recomanat'] : null;
    $actiu   = isset($data['actiu']) ? $data['actiu'] : null;

    try {

        $pdo->beginTransaction();

        $table = qi(Tables::DB_COMPTABILITAT_CATALEG_PRODUCTES, $pdo);

        // 🔎 comprovar existència
        $check = $pdo->prepare("SELECT id FROM {$table} WHERE id = :id LIMIT 1");
        $check->bindValue(':id', $id_bin, PDO::PARAM_LOB);
        $check->execute();

        if (!$check->fetchColumn()) {
            $pdo->rollBack();

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
            producte = :producte,
            descripcio = :descripcio,
            unitat = :unitat,
            preu_recomanat = :preu_recomanat,
            actiu = :actiu
        WHERE id = :id
    SQL;

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $id_bin, PDO::PARAM_LOB);
        $stmt->bindValue(':producte', $producte, PDO::PARAM_STR);
        $stmt->bindValue(':descripcio', $descripcio, PDO::PARAM_STR);
        $stmt->bindValue(':unitat', $unitat, PDO::PARAM_STR);
        $stmt->bindValue(':preu_recomanat', (float)$preu_recomanat, PDO::PARAM_STR);
        $stmt->bindValue(':actiu', $actiu, PDO::PARAM_INT);

        $stmt->execute();
        $pdo->commit();

        Response::success(
            MissatgesAPI::success('update'),
            ['id' => $id_bin],
            httpCode: 200
        );
    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
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
