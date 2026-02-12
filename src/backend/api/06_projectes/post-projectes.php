<?php

declare(strict_types=1);

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;

$slug = $routeParams[0] ?? null;

$db  = new Database();
$pdo = $db->getPdo();

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error(
        MissatgesAPI::error('method_not_allowed'),
        ['Method not allowed'],
        405
    );
    return;
}

/**
 * POST : Crear Projecte
 * URL: https://elliot.cat/api/projectes/post/createProjecte
 * BODY (json):
 * {
 *   "name": "Projecte X",
 *   "description": "...",
 *   "status": 1,
 *   "category_id": 2,
 *   "start_date": "2026-02-12",
 *   "end_date": "2026-03-01",
 *   "priority": 3,
 *   "client_id": 10,
 *   "budget_id": 5,
 *   "invoice_id": 7
 * }
 */
if ($slug === 'createProjecte') {
    // Auth (igual que al GET home)
    $userUuid = getAuthenticatedUserUuid(); // string UUID o null
    if (!$userUuid) {
        Response::error(MissatgesAPI::error('validacio'), ['Usuari no autenticat'], 401);
        return;
    }

    $userBin = uuidToBin($userUuid);
    if ($userBin === null) {
        Response::error(MissatgesAPI::error('validacio'), ['UUID invàlid'], 400);
        return;
    }

    // Leer JSON
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        Response::error(MissatgesAPI::error('bad_request'), ['json' => 'invalid'], 400);
        return;
    }

    // Helpers
    $errors = [];

    $str = static function ($v): string {
        return trim((string)$v);
    };

    $optStrOrNull = static function ($v): ?string {
        if ($v === null) return null;
        $s = trim((string)$v);
        return $s === '' ? null : $s;
    };

    $optIntOrNull = static function ($v): ?int {
        if ($v === null) return null;
        if ($v === '') return null;
        if (!is_numeric($v)) return null;
        $n = (int)$v;
        return $n;
    };

    $requireStr = static function (array $data, string $key, array &$errors) use ($str): ?string {
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
        if ($s === null) return true;
        // YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return false;
        [$y, $m, $d] = array_map('intval', explode('-', $s));
        return checkdate($m, $d, $y);
    };

    // Validación + defaults
    $name = $requireStr($data, 'name', $errors);
    if ($name !== null && mb_strlen($name) > 160) {
        $errors['name'] = 'max_160';
    }

    $description = $optStrOrNull($data['description'] ?? null);

    $status = $optIntOrNull($data['status'] ?? null);
    if ($status === null) $status = 1; // default DB
    if ($status < 0 || $status > 255) $errors['status'] = 'invalid';

    $priority = $optIntOrNull($data['priority'] ?? null);
    if ($priority === null) $priority = 3; // default DB
    if ($priority < 0 || $priority > 255) $errors['priority'] = 'invalid';

    $category_id = $optIntOrNull($data['category_id'] ?? null);
    $client_id   = $optIntOrNull($data['client_id'] ?? null);
    $budget_id   = $optIntOrNull($data['budget_id'] ?? null);
    $invoice_id  = $optIntOrNull($data['invoice_id'] ?? null);

    $start_date = $optStrOrNull($data['start_date'] ?? null);
    $end_date   = $optStrOrNull($data['end_date'] ?? null);

    if (!$isValidDate($start_date)) $errors['start_date'] = 'invalid_date';
    if (!$isValidDate($end_date))   $errors['end_date']   = 'invalid_date';
    if ($start_date !== null && $end_date !== null && $end_date < $start_date) {
        $errors['end_date'] = 'must_be_gte_start_date';
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        return;
    }

    // Insert
    $sql = <<<SQL
        INSERT INTO %s
        (name, description, status, category_id, start_date, end_date, priority, client_id, budget_id, invoice_id)
        VALUES
        (:name, :description, :status, :category_id, :start_date, :end_date, :priority, :client_id, :budget_id, :invoice_id)
    SQL;

    $q = sprintf($sql, qi(Tables::PROJECTES, $pdo));

    try {
        $stmt = $pdo->prepare($q);

        $stmt->bindValue(':name', $name, PDO::PARAM_STR);

        if ($description === null) $stmt->bindValue(':description', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':description', $description, PDO::PARAM_STR);

        $stmt->bindValue(':status', $status, PDO::PARAM_INT);
        $stmt->bindValue(':priority', $priority, PDO::PARAM_INT);

        // nullable ints
        $bindNullableInt = static function (\PDOStatement $st, string $param, ?int $val): void {
            if ($val === null) $st->bindValue($param, null, PDO::PARAM_NULL);
            else $st->bindValue($param, $val, PDO::PARAM_INT);
        };

        $bindNullableInt($stmt, ':category_id', $category_id);
        $bindNullableInt($stmt, ':client_id', $client_id);
        $bindNullableInt($stmt, ':budget_id', $budget_id);
        $bindNullableInt($stmt, ':invoice_id', $invoice_id);

        // nullable dates
        if ($start_date === null) $stmt->bindValue(':start_date', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':start_date', $start_date, PDO::PARAM_STR);

        if ($end_date === null) $stmt->bindValue(':end_date', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':end_date', $end_date, PDO::PARAM_STR);

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

        $newId = (int)$pdo->lastInsertId();

        Response::success(
            MissatgesAPI::success('create'),
            ['id' => $newId],
            201
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
