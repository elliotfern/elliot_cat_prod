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
        ORDER BY p.updated_at DESC
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

    /**
     * GET : Projecte per ID
     * URL: https://elliot.cat/api/projectes/get/id?id=123
     */
} else if ($slug === 'id') {
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

    // Validar id
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id || $id <= 0) {
        Response::error(MissatgesAPI::error('validacio'), ['id invàlid'], 400);
        return;
    }

    // Query: projecte + categoria
    $sql = <<<SQL
        SELECT
          p.id,
          p.name,
          p.description,
          p.status,
          p.category_id,
          c.name AS category_name,
          p.start_date,
          p.end_date,
          p.priority,
          p.client_id,
          p.budget_id,
          p.invoice_id,
          p.created_at,
          p.updated_at
        FROM %s AS p
        LEFT JOIN %s AS c ON c.id = p.category_id
        WHERE p.id = :id
        LIMIT 1
    SQL;

    $q = sprintf(
        $sql,
        qi(Tables::PROJECTES, $pdo),
        qi(Tables::PROJECTES_CATEGORIES, $pdo)
    );

    try {
        $row = $db->getData($q, [':id' => $id]);

        // getData suele devolver array de filas; pillamos la primera
        $item = is_array($row) && isset($row[0]) ? $row[0] : null;

        if (!$item) {
            Response::error(
                MissatgesAPI::error('not_found'),
                ['Projecte no trobat'],
                404
            );
            return;
        }

        Response::success(
            MissatgesAPI::success('get'),
            $item,
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
     * GET : Tasca per ID
     * URL: https://elliot.cat/api/tasks/get/tascaId?id=123
     */
} else if ($slug === 'tascaId') {

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

    // Validar id
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id || $id <= 0) {
        Response::error(MissatgesAPI::error('validacio'), ['id invàlid'], 400);
        return;
    }

    // Query: tasca (i opcionalment el nom del projecte)
    $sql = <<<SQL
        SELECT
          t.id,
          t.project_id,
          t.title,
          t.subject,
          t.notes,
          t.status,
          t.priority,
          t.planned_date,
          t.is_next,
          t.blocked_reason,
          t.estimated_hours,
          t.created_at,
          t.updated_at,
          t.done_at
          -- , p.name AS project_name
        FROM %s AS t
        -- LEFT JOIN %s AS p ON p.id = t.project_id
        WHERE t.id = :id
          AND t.user_id = :user_id
        LIMIT 1
    SQL;

    $q = sprintf(
        $sql,
        qi(Tables::PROJECTES_TASQUES, $pdo)
    );

    try {
        $row = $db->getData($q, [
            ':id' => $id,
            ':user_id' => $userBin,
        ]);

        // getData suele devolver array de filas; pillamos la primera
        $item = is_array($row) && isset($row[0]) ? $row[0] : null;

        if (!$item) {
            Response::error(
                MissatgesAPI::error('not_found'),
                ['Tasca no trobada'],
                404
            );
            return;
        }

        // Normalizaciones opcionales (si tu driver devuelve is_next como string, etc.)
        // $item['is_next'] = (int)($item['is_next'] ?? 0);
        // $item['status'] = (int)($item['status'] ?? 1);
        // $item['priority'] = (int)($item['priority'] ?? 3);

        Response::success(
            MissatgesAPI::success('get'),
            $item,
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
     * GET : Detalls projecte (fitxa completa)
     * URL: https://elliot.cat/api/projectes/get/detalls?id=123
     */
} else if ($slug === 'detalls') {

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

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id || $id <= 0) {
        Response::error(MissatgesAPI::error('validacio'), ['id invàlid'], 400);
        return;
    }

    $sql = <<<SQL
        SELECT
          p.id,
          p.name,
          p.description,
          p.status,
          p.category_id,
          c.name AS category_name,
          p.start_date,
          p.end_date,
          p.priority,
          p.client_id,
          cl.clientNom AS client_name,
          cl.clientCognoms AS client_cognoms,
          p.budget_id,
          p.invoice_id,
          p.created_at,
          p.updated_at
        FROM %s AS p
        LEFT JOIN %s AS c  ON c.id = p.category_id
        LEFT JOIN %s AS cl ON cl.id = p.client_id
        WHERE p.id = :id
        LIMIT 1
    SQL;

    $q = sprintf(
        $sql,
        qi(Tables::PROJECTES, $pdo),
        qi(Tables::PROJECTES_CATEGORIES, $pdo),
        qi(Tables::DB_COMPTABILITAT_CLIENTS, $pdo)
    );

    try {
        $row = $db->getData($q, [':id' => $id]);
        $item = is_array($row) && isset($row[0]) ? $row[0] : null;

        if (!$item) {
            Response::error(MissatgesAPI::error('not_found'), ['Projecte no trobat'], 404);
            return;
        }

        Response::success(MissatgesAPI::success('get'), $item, 200);
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    return;

    /**
     * GET : Tasques d'un projecte + KPIs
     * URL: https://elliot.cat/api/projectes/get/tasques?id=123
     */
} else if ($slug === 'tasques') {

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

    $projectId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$projectId || $projectId <= 0) {
        Response::error(MissatgesAPI::error('validacio'), ['id invàlid'], 400);
        return;
    }

    // (Opcional) paginación básica
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
    if (!$page || $page < 1) $page = 1;

    $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);
    if (!$limit || $limit < 1 || $limit > 200) $limit = 50;

    $offset = ($page - 1) * $limit;

    // 1) KPIs
    $sqlKpis = <<<SQL
        SELECT
          COUNT(*) AS total,
          SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) AS done,
          SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) AS blocked,
          SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS in_progress,
          SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS backlog,
          SUM(CASE WHEN is_next = 1 THEN 1 ELSE 0 END) AS next_count
        FROM %s
        WHERE project_id = :project_id
          AND user_id = :user_id
    SQL;

    $qKpis = sprintf($sqlKpis, qi(Tables::PROJECTES_TASQUES, $pdo));

    // 2) Items
    $sqlItems = <<<SQL
        SELECT
          id,
          project_id,
          title,
          subject,
          notes,
          status,
          priority,
          planned_date,
          is_next,
          blocked_reason,
          estimated_hours,
          created_at,
          updated_at,
          done_at
        FROM %s
        WHERE project_id = :project_id
          AND user_id = :user_id
        ORDER BY
          is_next DESC,
          priority DESC,
          planned_date IS NULL, planned_date ASC,
          id DESC
        LIMIT :limit OFFSET :offset
    SQL;

    $qItems = sprintf($sqlItems, qi(Tables::PROJECTES_TASQUES, $pdo));

    try {
        // KPIs
        $k = $pdo->prepare($qKpis);
        $k->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $k->bindValue(':user_id', $userBin, PDO::PARAM_LOB);
        $k->execute();
        $kpis = $k->fetch(PDO::FETCH_ASSOC) ?: [];

        // Items
        $st = $pdo->prepare($qItems);
        $st->bindValue(':project_id', $projectId, PDO::PARAM_INT);
        $st->bindValue(':user_id', $userBin, PDO::PARAM_LOB);
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();
        $items = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        Response::success(
            MissatgesAPI::success('get'),
            [
                'project' => ['id' => $projectId],
                'kpis' => [
                    'total' => (int)($kpis['total'] ?? 0),
                    'done' => (int)($kpis['done'] ?? 0),
                    'blocked' => (int)($kpis['blocked'] ?? 0),
                    'in_progress' => (int)($kpis['in_progress'] ?? 0),
                    'backlog' => (int)($kpis['backlog'] ?? 0),
                    'next' => (int)($kpis['next_count'] ?? 0),
                ],
                'page' => $page,
                'limit' => $limit,
                'items' => $items,
            ],
            200
        );
    } catch (PDOException $e) {
        Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
    }

    return;
} else {
    Response::error(
        MissatgesAPI::error('forbidden'),
        ['Slug no reconegut'],
        403
    );
}
