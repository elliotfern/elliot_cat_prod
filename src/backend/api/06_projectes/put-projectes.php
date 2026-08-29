<?php

declare(strict_types=1);

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Utils\Uuid;

$slug = $routeParams[0] ?? null;

$db  = new Database();
$pdo = $db->getPdo();

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');
corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    Response::error(
        MissatgesAPI::error('method_not_allowed'),
        ['Method not allowed'],
        405
    );
    return;
}

/**
 * PUT : Actualitzar Projecte
 * URL: https://elliot.cat/api/projectes/put/update
 * BODY (json) => update complet
 */
if ($slug === 'updateProjecte') {

    // ------------------------------------------------
    // Leer JSON
    // ------------------------------------------------

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {

        Response::error(
            MissatgesAPI::error('bad_request'),
            ['json' => 'invalid'],
            400
        );

        return;
    }

    // ------------------------------------------------
    // Helpers
    // ------------------------------------------------

    $errors = [];

    $str = static function ($v): string {
        return trim((string)$v);
    };

    $optStrOrNull = static function ($v): ?string {

        if ($v === null) {
            return null;
        }

        $s = trim((string)$v);

        return $s === '' ? null : $s;
    };

    $requireStr = static function (
        array $data,
        string $key,
        array &$errors
    ) use ($str): ?string {

        if (!isset($data[$key])) {

            $errors[$key] = 'required';

            return null;
        }

        $s = $str($data[$key]);

        if ($s === '') {

            $errors[$key] = 'required';

            return null;
        }

        return $s;
    };

    $isValidDate = static function (?string $s): bool {

        if ($s === null) {
            return true;
        }

        // YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return false;
        }

        [$y, $m, $d] = array_map(
            'intval',
            explode('-', $s)
        );

        return checkdate($m, $d, $y);
    };

    // ------------------------------------------------
    // ID - UUID
    // ------------------------------------------------

    $id = $requireStr($data, 'id', $errors);

    $idBin = null;

    if ($id !== null) {

        try {

            $idBin = Uuid::toBinary($id);
        } catch (\Throwable $e) {

            $errors['id'] = 'invalid_uuid';
        }
    }

    // ------------------------------------------------
    // Projecte
    // ------------------------------------------------

    $projecte = $requireStr(
        $data,
        'projecte',
        $errors
    );

    if (
        $projecte !== null &&
        mb_strlen($projecte) > 160
    ) {
        $errors['projecte'] = 'max_160';
    }

    // ------------------------------------------------
    // Descripció
    // ------------------------------------------------

    $descripcio = $optStrOrNull(
        $data['descripcio'] ?? null
    );

    // ------------------------------------------------
    // Estat
    // ------------------------------------------------

    $estat = $requireStr(
        $data,
        'estat',
        $errors
    );

    $estatsValids = [
        'pendent',
        'en_curs',
        'finalitzat',
        'arxivat',
    ];

    if (
        $estat !== null &&
        !in_array($estat, $estatsValids, true)
    ) {
        $errors['estat'] = 'invalid';
    }

    // ------------------------------------------------
    // Categoria
    // ------------------------------------------------

    $categoria = $requireStr(
        $data,
        'categoria',
        $errors
    );

    $categoriesValides = [
        'professional',
        'personal',
    ];

    if (
        $categoria !== null &&
        !in_array($categoria, $categoriesValides, true)
    ) {
        $errors['categoria'] = 'invalid';
    }

    // ------------------------------------------------
    // Prioritat
    // ------------------------------------------------

    $prioritat = $requireStr(
        $data,
        'prioritat',
        $errors
    );

    $prioritatsValides = [
        'baixa',
        'normal',
        'alta',
        'urgent',
    ];

    if (
        $prioritat !== null &&
        !in_array($prioritat, $prioritatsValides, true)
    ) {
        $errors['prioritat'] = 'invalid';
    }

    // ------------------------------------------------
    // UUID relacionats
    // ------------------------------------------------
    $client_id = $data['client_id'] ?? null;
    $pressupost_id = $data['pressupost_id'] ?? null;
    $factura_id = $data['factura_id'] ?? null;

    $uuidOrNull = static function (
        $value,
        string $key,
        array &$errors
    ): ?string {

        if ($value === null) {
            return null;
        }

        $value = trim((string)$value);

        if ($value === '') {
            return null;
        }

        try {

            return Uuid::toBinary($value);
        } catch (\Throwable $e) {

            $errors[$key] = 'invalid_uuid';

            return null;
        }
    };

    $client_id_bin = $uuidOrNull(
        $client_id,
        'client_id',
        $errors
    );

    $pressupost_id_bin = $uuidOrNull(
        $pressupost_id,
        'pressupost_id',
        $errors
    );

    $factura_id_bin = $uuidOrNull(
        $factura_id,
        'factura_id',
        $errors
    );

    // ------------------------------------------------
    // Dates
    // ------------------------------------------------

    $data_inici = $optStrOrNull(
        $data['data_inici'] ?? null
    );

    $data_fi = $optStrOrNull(
        $data['data_fi'] ?? null
    );

    if (!$isValidDate($data_inici)) {

        $errors['data_inici'] = 'invalid_date';
    }

    if (!$isValidDate($data_fi)) {

        $errors['data_fi'] = 'invalid_date';
    }

    if (
        $data_inici !== null &&
        $data_fi !== null &&
        $data_fi < $data_inici
    ) {

        $errors['data_fi'] = 'must_be_gte_data_inici';
    }

    // ------------------------------------------------
    // Errores de validación
    // ------------------------------------------------

    if (!empty($errors)) {

        Response::error(
            MissatgesAPI::error('invalid_data'),
            $errors,
            400
        );

        return;
    }

    // ------------------------------------------------
    // 1) Comprobar que existe
    // ------------------------------------------------

    $sqlExists = sprintf(

        "SELECT id
         FROM %s
         WHERE id = :id
         LIMIT 1",

        qi(Tables::PROJECTES, $pdo)
    );

    // ------------------------------------------------
    // 2) Update
    // ------------------------------------------------

    $sqlUpdate = <<<SQL

        UPDATE %s
        SET

            projecte = :projecte,
            descripcio = :descripcio,
            estat = :estat,
            categoria = :categoria,
            data_inici = :data_inici,
            data_fi = :data_fi,
            prioritat = :prioritat,
            client_id = :client_id,
            pressupost_id = :pressupost_id,
            factura_id = :factura_id
        WHERE id = :id
        LIMIT 1

    SQL;

    $qUpdate = sprintf(
        $sqlUpdate,
        qi(Tables::PROJECTES, $pdo)
    );

    try {

        // ------------------------------------------------
        // Exists?
        // ------------------------------------------------

        $stE = $pdo->prepare($sqlExists);

        $stE->bindValue(
            ':id',
            $idBin,
            PDO::PARAM_LOB
        );

        $stE->execute();

        $found = $stE->fetch(PDO::FETCH_ASSOC);

        if (!$found) {

            Response::error(
                MissatgesAPI::error('not_found'),
                ['Projecte no trobat'],
                404
            );

            return;
        }

        // ------------------------------------------------
        // Update
        // ------------------------------------------------

        $stmt = $pdo->prepare($qUpdate);

        $stmt->bindValue(
            ':id',
            $idBin,
            PDO::PARAM_LOB
        );

        $stmt->bindValue(
            ':projecte',
            $projecte,
            PDO::PARAM_STR
        );

        // Descripció
        if ($descripcio === null) {

            $stmt->bindValue(
                ':descripcio',
                null,
                PDO::PARAM_NULL
            );
        } else {

            $stmt->bindValue(
                ':descripcio',
                $descripcio,
                PDO::PARAM_STR
            );
        }

        // ENUMs
        $stmt->bindValue(
            ':estat',
            $estat,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':categoria',
            $categoria,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':prioritat',
            $prioritat,
            PDO::PARAM_STR
        );

        // ------------------------------------------------
        // UUIDs nullable
        // ------------------------------------------------

        if ($client_id_bin === null) {

            $stmt->bindValue(
                ':client_id',
                null,
                PDO::PARAM_NULL
            );
        } else {

            $stmt->bindValue(
                ':client_id',
                $client_id_bin,
                PDO::PARAM_LOB
            );
        }

        if ($pressupost_id_bin === null) {

            $stmt->bindValue(
                ':pressupost_id',
                null,
                PDO::PARAM_NULL
            );
        } else {

            $stmt->bindValue(
                ':pressupost_id',
                $pressupost_id_bin,
                PDO::PARAM_LOB
            );
        }

        if ($factura_id_bin === null) {

            $stmt->bindValue(
                ':factura_id',
                null,
                PDO::PARAM_NULL
            );
        } else {

            $stmt->bindValue(
                ':factura_id',
                $factura_id_bin,
                PDO::PARAM_LOB
            );
        }

        // ------------------------------------------------
        // Dates nullable
        // ------------------------------------------------

        if ($data_inici === null) {

            $stmt->bindValue(
                ':data_inici',
                null,
                PDO::PARAM_NULL
            );
        } else {

            $stmt->bindValue(
                ':data_inici',
                $data_inici,
                PDO::PARAM_STR
            );
        }

        if ($data_fi === null) {

            $stmt->bindValue(
                ':data_fi',
                null,
                PDO::PARAM_NULL
            );
        } else {

            $stmt->bindValue(
                ':data_fi',
                $data_fi,
                PDO::PARAM_STR
            );
        }

        // ------------------------------------------------
        // Execute
        // ------------------------------------------------

        if (!$stmt->execute()) {

            Response::error(

                MissatgesAPI::error('errorBD'),

                [
                    'sqlState' => $stmt->errorCode(),
                    'info' => $stmt->errorInfo(),
                ],

                500
            );

            return;
        }


        // ------------------------------------------------
        // Response
        // ------------------------------------------------

        Response::success(
            MissatgesAPI::success('update'),
            [
                'id' => $id
            ],
            httpCode: 200
        );
    } catch (PDOException $e) {

        Response::error(

            MissatgesAPI::error('errorBD'),

            [$e->getMessage()],

            500
        );
    }
    return;

    /**
     * PUT : Actualitzar Tasca
     * URL: https://elliot.cat/api/tasks/put/updateTask
     */
} else if ($slug === 'updateTask') {

    // ------------------------------------------------
    // Llegir JSON
    // ------------------------------------------------

    $raw = file_get_contents('php://input');

    $data = json_decode($raw, true);

    if (!is_array($data)) {

        Response::error(
            MissatgesAPI::error('bad_request'),
            ['json' => 'invalid'],
            400
        );

        return;
    }

    // ------------------------------------------------
    // Helpers
    // ------------------------------------------------

    $errors = [];

    $str = static function ($v): string {
        return trim((string)$v);
    };

    $optStrOrNull = static function ($v): ?string {

        if ($v === null) {
            return null;
        }

        $s = trim((string)$v);

        return $s === '' ? null : $s;
    };

    $optBool01 = static function ($v): int {

        if ($v === true || $v === 1 || $v === '1') {
            return 1;
        }

        return 0;
    };

    $optDecimalOrNull = static function ($v): ?string {

        if ($v === null || $v === '') {
            return null;
        }

        if (is_string($v)) {
            $v = str_replace(',', '.', trim($v));
        }

        if (!is_numeric($v)) {
            return null;
        }

        $n = (float)$v;

        if ($n < 0) {
            return null;
        }

        return number_format($n, 2, '.', '');
    };

    $requireStr = static function (
        array $data,
        string $key,
        array &$errors
    ) use ($str): ?string {

        if (!isset($data[$key])) {

            $errors[$key] = 'required';

            return null;
        }

        $s = $str($data[$key]);

        if ($s === '') {

            $errors[$key] = 'required';

            return null;
        }

        return $s;
    };

    $isValidDate = static function (?string $s): bool {

        if ($s === null) {
            return true;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return false;
        }

        [$y, $m, $d] = array_map(
            'intval',
            explode('-', $s)
        );

        return checkdate($m, $d, $y);
    };

    // ------------------------------------------------
    // ID de la tasca
    // ------------------------------------------------

    $id = $data['id'] ?? null;

    if (
        $id === null ||
        trim((string)$id) === ''
    ) {

        $errors['id'] = 'required';

        $idBin = null;
    } else {

        try {

            $idBin = Uuid::toBinary(
                trim((string)$id)
            );
        } catch (\Throwable $e) {

            $errors['id'] = 'invalid_uuid';

            $idBin = null;
        }
    }

    // ------------------------------------------------
    // Projecte
    // ------------------------------------------------

    $projecteId = $data['projecte_id'] ?? null;

    if (
        $projecteId === null ||
        trim((string)$projecteId) === ''
    ) {

        $errors['projecte_id'] = 'required';

        $projecteIdBin = null;
    } else {

        try {

            $projecteIdBin = Uuid::toBinary(
                trim((string)$projecteId)
            );
        } catch (\Throwable $e) {

            $errors['projecte_id'] = 'invalid_uuid';

            $projecteIdBin = null;
        }
    }

    // ------------------------------------------------
    // Títol
    // ------------------------------------------------

    $title = $requireStr(
        $data,
        'title',
        $errors
    );

    if (
        $title !== null &&
        mb_strlen($title) > 220
    ) {
        $errors['title'] = 'max_220';
    }

    // ------------------------------------------------
    // Subject
    // ------------------------------------------------

    $subject = $optStrOrNull(
        $data['subject'] ?? null
    );

    if (
        $subject !== null &&
        mb_strlen($subject) > 220
    ) {
        $errors['subject'] = 'max_220';
    }

    // ------------------------------------------------
    // Notes
    // ------------------------------------------------

    $notes = $optStrOrNull(
        $data['notes'] ?? null
    );

    // ------------------------------------------------
    // Estat
    // ------------------------------------------------

    $estat = $optStrOrNull(
        $data['estat'] ?? null
    );

    if ($estat === null) {
        $estat = 'pendent';
    }

    $estatsValids = [
        'pendent',
        'en_curs',
        'finalitzat',
        'arxivat',
    ];

    if (!in_array($estat, $estatsValids, true)) {
        $errors['estat'] = 'invalid';
    }

    // ------------------------------------------------
    // Prioritat
    // ------------------------------------------------

    $prioritat = $optStrOrNull(
        $data['prioritat'] ?? null
    );

    if ($prioritat === null) {
        $prioritat = 'normal';
    }

    $prioritatsValides = [
        'baixa',
        'normal',
        'alta',
        'urgent',
    ];

    if (!in_array($prioritat, $prioritatsValides, true)) {
        $errors['prioritat'] = 'invalid';
    }

    // ------------------------------------------------
    // Data planificada
    // ------------------------------------------------

    $plannedDate = $optStrOrNull(
        $data['planned_date'] ?? null
    );

    if (!$isValidDate($plannedDate)) {
        $errors['planned_date'] = 'invalid_date';
    }

    // ------------------------------------------------
    // Next
    // ------------------------------------------------

    $isNext = $optBool01(
        $data['is_next'] ?? 0
    );

    // ------------------------------------------------
    // Motiu bloqueig
    // ------------------------------------------------

    $blockedReason = $optStrOrNull(
        $data['blocked_reason'] ?? null
    );

    if (
        $blockedReason !== null &&
        mb_strlen($blockedReason) > 255
    ) {
        $errors['blocked_reason'] = 'max_255';
    }

    // ------------------------------------------------
    // Hores estimades
    // ------------------------------------------------

    $estimatedHours = $optDecimalOrNull(
        $data['estimated_hours'] ?? null
    );

    if (
        array_key_exists('estimated_hours', $data) &&
        $data['estimated_hours'] !== null &&
        $data['estimated_hours'] !== '' &&
        $estimatedHours === null
    ) {
        $errors['estimated_hours'] = 'invalid_decimal';
    }

    // ------------------------------------------------
    // Validació final
    // ------------------------------------------------

    if (!empty($errors)) {

        Response::error(
            MissatgesAPI::error('invalid_data'),
            $errors,
            400
        );

        return;
    }

    // ------------------------------------------------
    // Comprovar que existeix la tasca
    // ------------------------------------------------

    $sqlExists = sprintf(
        "
        SELECT id
        FROM %s
        WHERE id = :id
        LIMIT 1
        ",
        qi(Tables::PROJECTES_TASQUES, $pdo)
    );

    // ------------------------------------------------
    // Comprovar que existeix el projecte
    // ------------------------------------------------

    $sqlProjectExists = sprintf(
        "
        SELECT id
        FROM %s
        WHERE id = :projecte_id
        LIMIT 1
        ",
        qi(Tables::PROJECTES, $pdo)
    );

    // ------------------------------------------------
    // UPDATE
    // ------------------------------------------------

    $sqlUpdate = <<<SQL

        UPDATE %s

        SET
            projecte_id = :projecte_id,
            title = :title,
            subject = :subject,
            notes = :notes,
            estat = :estat,
            prioritat = :prioritat,
            planned_date = :planned_date,
            is_next = :is_next,
            blocked_reason = :blocked_reason,
            estimated_hours = :estimated_hours,
            done_at = :done_at

        WHERE id = :id
        LIMIT 1

    SQL;

    $qUpdate = sprintf(
        $sqlUpdate,
        qi(Tables::PROJECTES_TASQUES, $pdo)
    );

    try {

        // ------------------------------------------------
        // Existeix la tasca?
        // ------------------------------------------------

        $stTask = $pdo->prepare($sqlExists);

        $stTask->bindValue(
            ':id',
            $idBin,
            PDO::PARAM_LOB
        );

        $stTask->execute();

        $task = $stTask->fetch(PDO::FETCH_ASSOC);

        if (!$task) {

            Response::error(
                MissatgesAPI::error('not_found'),
                ['Tasca no trobada'],
                404
            );

            return;
        }

        // ------------------------------------------------
        // Existeix el projecte?
        // ------------------------------------------------

        $stProject = $pdo->prepare(
            $sqlProjectExists
        );

        $stProject->bindValue(
            ':projecte_id',
            $projecteIdBin,
            PDO::PARAM_LOB
        );

        $stProject->execute();

        $project = $stProject->fetch(
            PDO::FETCH_ASSOC
        );

        if (!$project) {

            Response::error(
                MissatgesAPI::error('not_found'),
                ['Projecte no trobat'],
                404
            );

            return;
        }

        // ------------------------------------------------
        // done_at
        // ------------------------------------------------

        $doneAt = null;

        if ($estat === 'finalitzat') {

            $doneAt = (new DateTime('now'))
                ->format('Y-m-d H:i:s');
        }

        // ------------------------------------------------
        // UPDATE
        // ------------------------------------------------

        $stmt = $pdo->prepare($qUpdate);

        $stmt->bindValue(
            ':id',
            $idBin,
            PDO::PARAM_LOB
        );

        $stmt->bindValue(
            ':projecte_id',
            $projecteIdBin,
            PDO::PARAM_LOB
        );

        $stmt->bindValue(
            ':title',
            $title,
            PDO::PARAM_STR
        );

        if ($subject === null) {

            $stmt->bindValue(
                ':subject',
                null,
                PDO::PARAM_NULL
            );
        } else {

            $stmt->bindValue(
                ':subject',
                $subject,
                PDO::PARAM_STR
            );
        }

        if ($notes === null) {

            $stmt->bindValue(
                ':notes',
                null,
                PDO::PARAM_NULL
            );
        } else {

            $stmt->bindValue(
                ':notes',
                $notes,
                PDO::PARAM_STR
            );
        }

        // ENUM
        $stmt->bindValue(
            ':estat',
            $estat,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':prioritat',
            $prioritat,
            PDO::PARAM_STR
        );

        // Data
        if ($plannedDate === null) {

            $stmt->bindValue(
                ':planned_date',
                null,
                PDO::PARAM_NULL
            );
        } else {

            $stmt->bindValue(
                ':planned_date',
                $plannedDate,
                PDO::PARAM_STR
            );
        }

        // is_next
        $stmt->bindValue(
            ':is_next',
            $isNext,
            PDO::PARAM_INT
        );

        // blocked_reason
        if ($blockedReason === null) {

            $stmt->bindValue(
                ':blocked_reason',
                null,
                PDO::PARAM_NULL
            );
        } else {

            $stmt->bindValue(
                ':blocked_reason',
                $blockedReason,
                PDO::PARAM_STR
            );
        }

        // estimated_hours
        if ($estimatedHours === null) {

            $stmt->bindValue(
                ':estimated_hours',
                null,
                PDO::PARAM_NULL
            );
        } else {

            $stmt->bindValue(
                ':estimated_hours',
                $estimatedHours,
                PDO::PARAM_STR
            );
        }

        // done_at
        if ($doneAt === null) {

            $stmt->bindValue(
                ':done_at',
                null,
                PDO::PARAM_NULL
            );
        } else {

            $stmt->bindValue(
                ':done_at',
                $doneAt,
                PDO::PARAM_STR
            );
        }

        // ------------------------------------------------
        // Execute
        // ------------------------------------------------

        if (!$stmt->execute()) {

            Response::error(
                MissatgesAPI::error('errorBD'),
                [
                    'sqlState' => $stmt->errorCode(),
                    'info' => $stmt->errorInfo(),
                ],
                500
            );

            return;
        }

        // ------------------------------------------------
        // Response
        // ------------------------------------------------

        Response::success(
            MissatgesAPI::success('update'),
            [
                'id' => $id,
            ],
            httpCode: 200
        );
    } catch (PDOException $e) {

        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    return;
}

Response::error(
    MissatgesAPI::error('forbidden'),
    ['Slug no reconegut'],
    403
);
