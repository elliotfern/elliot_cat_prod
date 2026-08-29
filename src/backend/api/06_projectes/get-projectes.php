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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://dev.elliot.local']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://dev.elliot.local']);

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

    // ------------------------------------------------
    // TODAY
    // ------------------------------------------------

    $sqlToday = <<<SQL
        SELECT
            t.id,
            t.projecte_id,
            p.projecte AS projecte,
            t.title,
            t.estat,
            t.prioritat,
            t.planned_date,
            t.is_next,
            t.blocked_reason,
            t.estimated_hours,
            t.updated_at

        FROM %s AS t

        LEFT JOIN %s AS p
            ON p.id = t.projecte_id

        WHERE t.planned_date = CURDATE()
          AND t.estat <> 'arxivat'

        ORDER BY
            t.prioritat ASC,
            t.updated_at DESC
    SQL;

    // ------------------------------------------------
    // BLOCKED
    // ------------------------------------------------

    $sqlBlocked = <<<SQL
        SELECT
            t.id,
            t.projecte_id,
            p.projecte AS projecte,
            t.title,
            t.estat,
            t.prioritat,
            t.planned_date,
            t.is_next,
            t.blocked_reason,
            t.estimated_hours,
            t.updated_at

        FROM %s AS t

        LEFT JOIN %s AS p
            ON p.id = t.projecte_id

        WHERE t.estat = 'en_curs'

        ORDER BY
            t.prioritat ASC,
            t.updated_at DESC
    SQL;

    // ------------------------------------------------
    // ACTIVE PROJECTS + NEXT TASK
    // ------------------------------------------------

    $sqlActive = <<<SQL
        SELECT
            p.id AS project_id,
            p.projecte AS project_name,
            p.prioritat AS project_priority,
            p.categoria AS category_name,

            t.id AS next_task_id,
            t.title AS next_task_title,
            t.estat AS next_task_status,
            t.prioritat AS next_task_priority,
            t.blocked_reason AS blocked_reason

        FROM %s AS p

        LEFT JOIN %s AS t
            ON t.projecte_id = p.id
           AND t.is_next = 1

        WHERE p.estat = 'en_curs'

        ORDER BY
            p.updated_at DESC
    SQL;

    // ------------------------------------------------
    // Queries
    // ------------------------------------------------

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
        qi(Tables::PROJECTES_TASQUES, $pdo)
    );

    // ------------------------------------------------
    // Execute
    // ------------------------------------------------

    try {

        $params = [];

        $today = $db->getData(
            $qToday,
            $params
        ) ?: [];

        $blocked = $db->getData(
            $qBlocked,
            $params
        ) ?: [];

        $activeProjects = $db->getData(
            $qActive
        ) ?: [];

        Response::success(
            MissatgesAPI::success('get'),
            [
                'today' => $today,
                'blocked' => $blocked,
                'activeProjects' => $activeProjects,
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


    /**
     * GET : Projecte per ID
     * URL: https://elliot.cat/api/projectes/get/id?id=123
     */
} else if ($slug === 'id') {

    // ------------------------------------------------
    // Validar UUID
    // ------------------------------------------------

    $id = $_GET['id'] ?? null;

    if (!is_string($id) || trim($id) === '') {

        Response::error(
            MissatgesAPI::error('validacio'),
            ['id invàlid'],
            400
        );

        return;
    }

    $id = trim($id);

    try {

        $idBin = Uuid::toBinary($id);
    } catch (\Throwable $e) {

        Response::error(
            MissatgesAPI::error('validacio'),
            ['id invàlid'],
            400
        );

        return;
    }

    // ------------------------------------------------
    // Query : projecte
    // ------------------------------------------------

    $sql = <<<SQL
        SELECT
            p.id,
            p.projecte,
            p.descripcio,
            p.estat,
            p.categoria,
            p.data_inici,
            p.data_fi,
            p.prioritat,
            p.client_id,
            p.pressupost_id,
            p.factura_id,
            p.created_at,
            p.updated_at

        FROM %s AS p

        WHERE p.id = :id

        LIMIT 1
    SQL;

    $q = sprintf(
        $sql,
        qi(Tables::PROJECTES, $pdo)
    );

    try {

        $row = $db->getData(
            $q,
            [':id' => $idBin]
        );

        // getData suele devolver array de filas
        $item = is_array($row) && isset($row[0])
            ? $row[0]
            : null;

        if (!$item) {

            Response::error(
                MissatgesAPI::error('not_found'),
                ['Projecte no trobat'],
                404
            );

            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $item,
            httpCode: 200
        );
    } catch (PDOException $e) {

        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    /**
     * GET : Llistat complert de projectes
     * URL: https://elliot.cat/api/projectes/get/llistatProjectes
     */
} else if ($slug === 'llistatProjectes') {

    // ------------------------------------------------
    // Query : llistat de projectes
    // ------------------------------------------------

    $sql = <<<SQL
        SELECT
            p.id,
            p.projecte,
            p.descripcio,
            p.estat,
            p.categoria,
            p.data_inici,
            p.data_fi,
            p.prioritat,
            p.client_id,
            p.pressupost_id,
            p.factura_id,
            p.created_at,
            p.updated_at,
            c.nom,
            c.cognoms,
            c.empresa
        FROM %s AS p
        LEFT JOIN %s AS c ON p.client_id = c.id
        ORDER BY p.data_inici DESC
    SQL;

    $q = sprintf(
        $sql,
        qi(Tables::PROJECTES, $pdo),
        qi(Tables::DB_CONTACTES, $pdo),
    );

    try {

        $data = $db->getData(
            $q,
            [],
            false
        );

        if (!$data) {

            Response::error(
                MissatgesAPI::error('not_found'),
                ['Projectes no trobats'],
                404
            );

            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $data,
            httpCode: 200
        );
    } catch (PDOException $e) {

        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }


    /**
     * GET : Llistat complert de tasques
     * URL: https://elliot.cat/api/projectes/get/llistatTasques
     */
} else if ($slug === 'llistatTasques') {

    // ------------------------------------------------
    // Query : llistat de tasques
    // ------------------------------------------------

    $sql = <<<SQL
        SELECT
            t.id,
            t.projecte_id,
            t.title,
            t.subject,
            t.notes,
            t.estat,
            t.prioritat,
            t.planned_date,
            t.is_next,
            t.blocked_reason,
            t.estimated_hours,
            t.created_at,
            t.updated_at,
            t.done_at,

            p.projecte AS projecte,

            c.nom,
            c.cognoms,
            c.empresa,
            p.client_id AS client_id
        FROM %s AS t
        LEFT JOIN %s AS p ON t.projecte_id = p.id
        LEFT JOIN %s AS c ON p.client_id = c.id
        ORDER BY p.data_inici DESC
    SQL;

    $q = sprintf(
        $sql,
        qi(Tables::PROJECTES_TASQUES, $pdo),
        qi(Tables::PROJECTES, $pdo),
        qi(Tables::DB_CONTACTES, $pdo),
    );

    try {

        $data = $db->getData($q, [], false);

        if (!$data) {

            Response::error(
                MissatgesAPI::error('not_found'),
                ['Projectes no trobats'],
                404
            );

            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $data,
            httpCode: 200
        );
    } catch (PDOException $e) {

        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    /**
     * GET : Tasca per ID
     * URL: https://elliot.cat/api/tasks/get/tascaId?id=123
     */
} else if ($slug === 'tascaId') {

    // ------------------------------------------------
    // Validar UUID
    // ------------------------------------------------

    $id = $_GET['id'] ?? null;

    if (!is_string($id) || trim($id) === '') {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['id invàlid'],
            400
        );
        return;
    }

    try {
        $idBin = Uuid::toBinary($id);
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['id invàlid'],
            400
        );
        return;
    }

    // ------------------------------------------------
    // Query
    // ------------------------------------------------

    $sql = <<<SQL
        SELECT
          t.id,
          t.projecte_id,
          t.title,
          t.subject,
          t.notes,
          t.estat,
          t.prioritat,
          t.planned_date,
          t.is_next,
          t.blocked_reason,
          t.estimated_hours,
          t.created_at,
          t.updated_at,
          t.done_at,
          p.projecte AS projecte
        FROM %s AS t
        LEFT JOIN %s AS p
          ON p.id = t.projecte_id
        WHERE t.id = :id
        LIMIT 1
    SQL;

    $q = sprintf(
        $sql,
        qi(Tables::PROJECTES_TASQUES, $pdo),
        qi(Tables::PROJECTES, $pdo)
    );

    try {

        $row = $db->getData(
            $q,
            [
                ':id' => $idBin,
            ]
        );

        // getData devuelve array de filas
        $item = is_array($row) && isset($row[0])
            ? $row[0]
            : null;

        if (!$item) {
            Response::error(
                MissatgesAPI::error('not_found'),
                ['Tasca no trobada'],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $item,
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
     * GET : Detalls projecte (fitxa completa)
     * URL: https://elliot.cat/api/projectes/get/detalls?id=123
     */
} else if ($slug === 'detalls') {

    $userId = '019710e490e4735f896af9d11d7106a1';
    $userBin = Uuid::toBinary($userId);

    // ------------------------------------------------
    // ID projecte
    // ------------------------------------------------

    $id = $_GET['id'] ?? null;

    if (!is_string($id) || trim($id) === '') {

        Response::error(
            MissatgesAPI::error('validacio'),
            ['id invàlid'],
            400
        );

        return;
    }

    $id = trim($id);

    try {

        $idBin = Uuid::toBinary($id);
    } catch (\Throwable $e) {

        Response::error(
            MissatgesAPI::error('validacio'),
            ['id invàlid'],
            400
        );

        return;
    }

    // ------------------------------------------------
    // Query
    // ------------------------------------------------

    $sql = <<<SQL
        SELECT
            p.id,
            p.projecte,
            p.descripcio,
            p.estat,
            p.categoria,
            p.data_inici,
            p.data_fi,
            p.prioritat,

            p.client_id,

            cl.nom,
            cl.cognoms,
            cl.empresa,

            p.pressupost_id,
            p.factura_id,

            p.created_at,
            p.updated_at

        FROM %s AS p

        LEFT JOIN %s AS cl
            ON cl.id = p.client_id

        WHERE p.id = :id

        LIMIT 1
    SQL;

    $q = sprintf(
        $sql,
        qi(Tables::PROJECTES, $pdo),
        qi(Tables::DB_CONTACTES, $pdo)
    );

    try {

        $row = $db->getData(
            $q,
            [
                ':id' => $idBin,
            ]
        );

        $item = is_array($row) && isset($row[0])
            ? $row[0]
            : null;

        if (!$item) {

            Response::error(
                MissatgesAPI::error('not_found'),
                ['Projecte no trobat'],
                404
            );

            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $item,
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
     * GET : Tasques d'un projecte + KPIs
     * URL: https://elliot.cat/api/projectes/get/tasques?id=123
     */
} else if ($slug === 'tasques') {

    // ------------------------------------------------
    // Validar UUID del projecte
    // ------------------------------------------------

    $projectId = $_GET['id'] ?? null;

    if (!is_string($projectId) || trim($projectId) === '') {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['id invàlid'],
            400
        );
        return;
    }

    try {
        $projectIdBin = Uuid::toBinary($projectId);
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['id invàlid'],
            400
        );
        return;
    }

    // ------------------------------------------------
    // Query
    // ------------------------------------------------

    $sql = <<<SQL
        SELECT
          id,
          projecte_id,
          title,
          subject,
          notes,
          estat,
          prioritat,
          planned_date,
          is_next,
          blocked_reason,
          estimated_hours,
          created_at,
          updated_at,
          done_at
        FROM %s
        WHERE projecte_id = :projecte_id
        ORDER BY
          is_next DESC,
          planned_date IS NULL,
          planned_date ASC,
          created_at DESC
    SQL;

    $q = sprintf(
        $sql,
        qi(Tables::PROJECTES_TASQUES, $pdo)
    );

    try {

        $data = $db->getData(
            $q,
            [
                ':projecte_id' => $projectIdBin,
            ],
            false
        ) ?: [];

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $data,
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
} else {
    Response::error(
        MissatgesAPI::error('forbidden'),
        ['Slug no reconegut'],
        403
    );
}
