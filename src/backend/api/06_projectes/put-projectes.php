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
corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

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
    // Auth (igual que home / create)
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
        return (int)$v;
    };

    $requireInt = static function (array $data, string $key, array &$errors): ?int {
        if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === null) {
            $errors[$key] = 'required';
            return null;
        }
        if (!is_numeric($data[$key])) {
            $errors[$key] = 'invalid_int';
            return null;
        }
        $n = (int)$data[$key];
        if ($n <= 0) {
            $errors[$key] = 'must_be_gt_0';
            return null;
        }
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
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return false;
        [$y, $m, $d] = array_map('intval', explode('-', $s));
        return checkdate($m, $d, $y);
    };

    // Validación obligatoria
    $id = $requireInt($data, 'id', $errors);

    $name = $requireStr($data, 'name', $errors);
    if ($name !== null && mb_strlen($name) > 160) {
        $errors['name'] = 'max_160';
    }

    // Campos opcionales + defaults
    $description = $optStrOrNull($data['description'] ?? null);

    $status = $optIntOrNull($data['status'] ?? null);
    if ($status === null) $status = 1;
    if ($status < 0 || $status > 255) $errors['status'] = 'invalid';

    $priority = $optIntOrNull($data['priority'] ?? null);
    if ($priority === null) $priority = 3;
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

    // 1) Comprobar que existe (404 si no)
    $sqlExists = sprintf(
        "SELECT id FROM %s WHERE id = :id LIMIT 1",
        qi(Tables::PROJECTES, $pdo)
    );

    // 2) Update
    $sqlUpdate = <<<SQL
        UPDATE %s
        SET
          name = :name,
          description = :description,
          status = :status,
          category_id = :category_id,
          start_date = :start_date,
          end_date = :end_date,
          priority = :priority,
          client_id = :client_id,
          budget_id = :budget_id,
          invoice_id = :invoice_id
        WHERE id = :id
        LIMIT 1
    SQL;

    $qUpdate = sprintf($sqlUpdate, qi(Tables::PROJECTES, $pdo));

    try {
        // Exists?
        $stE = $pdo->prepare($sqlExists);
        $stE->bindValue(':id', $id, PDO::PARAM_INT);
        $stE->execute();
        $found = $stE->fetch(PDO::FETCH_ASSOC);

        if (!$found) {
            Response::error(MissatgesAPI::error('not_found'), ['Projecte no trobat'], 404);
            return;
        }

        $stmt = $pdo->prepare($qUpdate);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);

        if ($description === null) $stmt->bindValue(':description', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':description', $description, PDO::PARAM_STR);

        $stmt->bindValue(':status', $status, PDO::PARAM_INT);
        $stmt->bindValue(':priority', $priority, PDO::PARAM_INT);

        $bindNullableInt = static function (\PDOStatement $st, string $param, ?int $val): void {
            if ($val === null) $st->bindValue($param, null, PDO::PARAM_NULL);
            else $st->bindValue($param, $val, PDO::PARAM_INT);
        };

        $bindNullableInt($stmt, ':category_id', $category_id);
        $bindNullableInt($stmt, ':client_id', $client_id);
        $bindNullableInt($stmt, ':budget_id', $budget_id);
        $bindNullableInt($stmt, ':invoice_id', $invoice_id);

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

        // (Opcional pero útil) devolver updated_at real después del ON UPDATE
        $sqlAfter = sprintf(
            "SELECT updated_at FROM %s WHERE id = :id LIMIT 1",
            qi(Tables::PROJECTES, $pdo)
        );
        $stA = $pdo->prepare($sqlAfter);
        $stA->bindValue(':id', $id, PDO::PARAM_INT);
        $stA->execute();
        $after = $stA->fetch(PDO::FETCH_ASSOC);

        Response::success(
            MissatgesAPI::success('update'),
            [
                'id' => $id,
                'updated_at' => $after['updated_at'] ?? null,
            ],
            200
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
     * BODY (json):
     * {
     *   "id": 123,
     *   "project_id": 12,
     *   "title": "Fer X",
     *   "subject": "Opcional",
     *   "notes": "Opcional",
     *   "status": 2,
     *   "priority": 3,
     *   "planned_date": "2026-02-13",
     *   "is_next": 1,
     *   "blocked_reason": null,
     *   "estimated_hours": 1.75
     * }
     */
} else if ($slug === 'updateTask') {

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

    // Helpers (mismos que en POST)
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
        if ($v === true || $v === 1 || $v === '1') return 1;
        return 0;
    };

    $optDecimalOrNull = static function ($v): ?string {
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
    $id = $optIntOrNull($data['id'] ?? null);
    if ($id === null || $id <= 0) $errors['id'] = 'required';

    $title = $requireStr($data, 'title', $errors);
    if ($title !== null && mb_strlen($title) > 220) $errors['title'] = 'max_220';

    $subject = $optStrOrNull($data['subject'] ?? null);
    if ($subject !== null && mb_strlen($subject) > 220) $errors['subject'] = 'max_220';

    $notes = $optStrOrNull($data['notes'] ?? null);

    $status = $optIntOrNull($data['status'] ?? null);
    if ($status === null) $status = 1;
    if (!in_array($status, [1, 2, 3, 4], true)) $errors['status'] = 'invalid';

    $priority = $optIntOrNull($data['priority'] ?? null);
    if ($priority === null) $priority = 3;
    if (!in_array($priority, [1, 2, 3, 4], true)) $errors['priority'] = 'invalid';

    $project_id = $optIntOrNull($data['project_id'] ?? null);
    if ($project_id !== null && $project_id <= 0) $errors['project_id'] = 'invalid';

    $planned_date = $optStrOrNull($data['planned_date'] ?? null);
    if (!$isValidDate($planned_date)) $errors['planned_date'] = 'invalid_date';

    $is_next = $optBool01($data['is_next'] ?? 0);

    $blocked_reason = $optStrOrNull($data['blocked_reason'] ?? null);
    if ($blocked_reason !== null && mb_strlen($blocked_reason) > 255) $errors['blocked_reason'] = 'max_255';

    if ($status === 3 && ($blocked_reason === null || $blocked_reason === '')) {
        $errors['blocked_reason'] = 'required_when_blocked';
    }

    $estimated_hours = $optDecimalOrNull($data['estimated_hours'] ?? null);
    if (($data['estimated_hours'] ?? null) !== null && $estimated_hours === null) {
        $errors['estimated_hours'] = 'invalid_decimal';
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('invalid_data'), $errors, 400);
        return;
    }

    // Update (done_at controlado por status)
    // - Si status=4: done_at = COALESCE(done_at, NOW())
    // - Si status!=4: done_at = NULL
    $sql = <<<SQL
        UPDATE %s
        SET
          project_id      = :project_id,
          title           = :title,
          subject         = :subject,
          notes           = :notes,
          status          = :status,
          priority        = :priority,
          planned_date    = :planned_date,
          is_next         = :is_next,
          blocked_reason  = :blocked_reason,
          estimated_hours = :estimated_hours,
          done_at         = CASE
                              WHEN :status = 4 THEN COALESCE(done_at, CURRENT_TIMESTAMP())
                              ELSE NULL
                            END
        WHERE id = :id
          AND user_id = :user_id
        LIMIT 1
    SQL;

    $q = sprintf($sql, qi(Tables::PROJECTES_TASQUES, $pdo));

    try {
        $stmt = $pdo->prepare($q);

        // nullable int
        if ($project_id === null) $stmt->bindValue(':project_id', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);

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

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userBin, PDO::PARAM_LOB);

        if (!$stmt->execute()) {
            Response::error(
                MissatgesAPI::error('errorBD'),
                ['sqlState' => $stmt->errorCode(), 'info' => $stmt->errorInfo()],
                500
            );
            return;
        }

        if ($stmt->rowCount() < 1) {
            Response::error(MissatgesAPI::error('not_found'), ['Tasca no trobada'], 404);
            return;
        }

        Response::success(
            MissatgesAPI::success('update'),
            ['id' => (int)$id],
            200
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
