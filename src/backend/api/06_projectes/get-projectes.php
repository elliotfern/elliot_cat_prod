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
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error(
        MissatgesAPI::error('method_not_allowed'),
        ['Method not allowed'],
        405
    );
    return;
}

/**
 * GET : Home Gestor Projectes
 * URL: https://elliot.cat/api/projectes/get/home
 */
if ($slug === 'home') {
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

    // TODAY
    $sqlToday = <<<SQL
        SELECT
          t.id,
          t.project_id,
          p.name AS project_name,
          t.title,
          t.status,
          t.priority,
          t.planned_date,
          t.is_next,
          t.blocked_reason,
          t.estimated_hours,
          t.updated_at
        FROM %s AS t
        LEFT JOIN %s AS p ON p.id = t.project_id
        WHERE t.user_id = :user_id
          AND t.planned_date = CURDATE()
          AND t.status <> 4
        ORDER BY t.priority ASC, t.updated_at DESC
    SQL;

    // BLOCKED
    $sqlBlocked = <<<SQL
        SELECT
          t.id,
          t.project_id,
          p.name AS project_name,
          t.title,
          t.status,
          t.priority,
          t.planned_date,
          t.is_next,
          t.blocked_reason,
          t.estimated_hours,
          t.updated_at
        FROM %s AS t
        LEFT JOIN %s AS p ON p.id = t.project_id
        WHERE t.user_id = :user_id
          AND t.status = 3
        ORDER BY t.priority ASC, t.updated_at DESC
    SQL;

    // ACTIVE PROJECTS + NEXT TASK (no depende de user_id, es “proyectos activos” globales)
    // Si quieres que sea “solo proyectos donde tengo tareas”, lo filtramos luego.
    $sqlActive = <<<SQL
        SELECT
          p.id AS project_id,
          p.name AS project_name,
          p.priority AS project_priority,
          c.name AS category_name,

          t.id AS next_task_id,
          t.title AS next_task_title,
          t.status AS next_task_status,
          t.priority AS next_task_priority,
          t.blocked_reason AS blocked_reason
        FROM %s AS p
        LEFT JOIN %s AS c ON c.id = p.category_id
        LEFT JOIN %s AS t ON t.project_id = p.id AND t.is_next = 1
        WHERE p.status = 1
        ORDER BY p.priority ASC, p.updated_at DESC
    SQL;

    $qToday = sprintf(
        $sqlToday,
        qi(Tables::PROJECTES_TASQUES, $pdo),
        qi(Tables::PROJECTES, $pdo)
    );

    $qBlocked = sprintf(
        $sqlBlocked,
        qi(Tables::PROJECTES_TASQUES, $pdo),
        qi(Tables::PROJECTES, $pdo)
    );

    $qActive = sprintf(
        $sqlActive,
        qi(Tables::PROJECTES, $pdo),
        qi(Tables::PROJECTES_CATEGORIES, $pdo),
        qi(Tables::PROJECTES_TASQUES, $pdo)
    );

    try {
        $params = [':user_id' => $userBin];

        $today         = $db->getData($qToday, $params) ?: [];
        $blocked       = $db->getData($qBlocked, $params) ?: [];
        $activeProjects = $db->getData($qActive) ?: [];

        Response::success(
            MissatgesAPI::success('get'),
            [
                'today' => $today,
                'blocked' => $blocked,
                'activeProjects' => $activeProjects,
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
}

Response::error(
    MissatgesAPI::error('forbidden'),
    ['Slug no reconegut'],
    403
);
