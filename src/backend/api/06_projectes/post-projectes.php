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

    /**
     * POST : Crear Tasca
     * URL: https://elliot.cat/api/tasks/post/createTask
     * BODY (json):
     * {
     *   "project_id": 12,
     *   "title": "Fer X",
     *   "subject": "Opcional",
     *   "notes": "Opcional",
     *   "status": 1,
     *   "priority": 3,
     *   "planned_date": "2026-02-13",
     *   "is_next": 0,
     *   "blocked_reason": null,
     *   "estimated_hours": 2.50
     * }
     */
} else if ($slug === 'createTask') {

    // Auth
    $userUuid = getAuthenticatedUserUuid();
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
        if ($v === null || $v === '') return null;
        if (!is_numeric($v)) return null;
        return (int)$v;
    };

    $optBool01 = static function ($v): int {
        // acepta 1/0, true/false, "1"/"0"
        if ($v === true || $v === 1 || $v === '1') return 1;
        return 0;
    };

    $optDecimalOrNull = static function ($v): ?string {
        // devolvemos string decimal para bind STR sin líos de coma flotante
        if ($v === null || $v === '') return null;
        if (is_string($v)) $v = str_replace(',', '.', trim($v));
        if (!is_numeric($v)) return null;
        $n = (float)$v;
        if ($n < 0) return null;
        return number_format($n, 2, '.', '');
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
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return false;
        [$y, $m, $d] = array_map('intval', explode('-', $s));
        return checkdate($m, $d, $y);
    };

    // Validación + defaults
    $title = $requireStr($data, 'title', $errors);
    if ($title !== null && mb_strlen($title) > 220) $errors['title'] = 'max_220';

    $subject = $optStrOrNull($data['subject'] ?? null);
    if ($subject !== null && mb_strlen($subject) > 220) $errors['subject'] = 'max_220';

    $notes = $optStrOrNull($data['notes'] ?? null);

    $status = $optIntOrNull($data['status'] ?? null);
    if ($status === null) $status = 1; // default DB
    if (!in_array($status, [1, 2, 3, 4], true)) $errors['status'] = 'invalid';

    $priority = $optIntOrNull($data['priority'] ?? null);
    if ($priority === null) $priority = 3; // default DB
    if (!in_array($priority, [1, 2, 3, 4], true)) $errors['priority'] = 'invalid';

    $project_id = $optIntOrNull($data['project_id'] ?? null);
    if ($project_id !== null && $project_id <= 0) $errors['project_id'] = 'invalid';

    $planned_date = $optStrOrNull($data['planned_date'] ?? null);
    if (!$isValidDate($planned_date)) $errors['planned_date'] = 'invalid_date';

    $is_next = $optBool01($data['is_next'] ?? 0);

    $blocked_reason = $optStrOrNull($data['blocked_reason'] ?? null);
    if ($blocked_reason !== null && mb_strlen($blocked_reason) > 255) $errors['blocked_reason'] = 'max_255';

    // Si está bloqueada, pedimos motivo
    if ($status === 3 && ($blocked_reason === null || $blocked_reason === '')) {
        $errors['blocked_reason'] = 'required_when_blocked';
    }

    $estimated_hours = $optDecimalOrNull($data['estimated_hours'] ?? null);
    if (($data['estimated_hours'] ?? null) !== null && $estimated_hours === null) {
        // han enviado algo pero no es válido
        $errors['estimated_hours'] = 'invalid_decimal';
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        return;
    }

    // done_at: si crean ya como "Feta", lo ponemos ahora
    $done_at = null;
    if ($status === 4) {
        $done_at = (new DateTime('now'))->format('Y-m-d H:i:s');
    }

    // Insert
    $sql = <<<SQL
        INSERT INTO %s
        (project_id, user_id, title, subject, notes, status, priority, planned_date, is_next, blocked_reason, estimated_hours, done_at)
        VALUES
        (:project_id, :user_id, :title, :subject, :notes, :status, :priority, :planned_date, :is_next, :blocked_reason, :estimated_hours, :done_at)
    SQL;

    $q = sprintf($sql, qi(Tables::PROJECTES_TASQUES, $pdo));

    try {
        $stmt = $pdo->prepare($q);

        // nullable int
        if ($project_id === null) $stmt->bindValue(':project_id', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);

        $stmt->bindValue(':user_id', $userBin, PDO::PARAM_LOB);

        $stmt->bindValue(':title', $title, PDO::PARAM_STR);

        if ($subject === null) $stmt->bindValue(':subject', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':subject', $subject, PDO::PARAM_STR);

        if ($notes === null) $stmt->bindValue(':notes', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':notes', $notes, PDO::PARAM_STR);

        $stmt->bindValue(':status', $status, PDO::PARAM_INT);
        $stmt->bindValue(':priority', $priority, PDO::PARAM_INT);

        if ($planned_date === null) $stmt->bindValue(':planned_date', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':planned_date', $planned_date, PDO::PARAM_STR);

        $stmt->bindValue(':is_next', $is_next, PDO::PARAM_INT);

        if ($blocked_reason === null) $stmt->bindValue(':blocked_reason', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':blocked_reason', $blocked_reason, PDO::PARAM_STR);

        if ($estimated_hours === null) $stmt->bindValue(':estimated_hours', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':estimated_hours', $estimated_hours, PDO::PARAM_STR);

        if ($done_at === null) $stmt->bindValue(':done_at', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':done_at', $done_at, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            Response::error(
                MissatgesAPI::error('errorBD'),
                ['sqlState' => $stmt->errorCode(), 'info' => $stmt->errorInfo()],
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
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    return;
}

Response::error(
    MissatgesAPI::error('forbidden'),
    ['Slug no reconegut'],
    403
);
