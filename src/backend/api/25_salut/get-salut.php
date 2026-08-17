<?php

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Utils\Uuid;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

/*
 * BACKEND SALUT
 * GET SALUT
 */

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

/**
 * GET : Llistat de patologies
 * URL: https://elliot.cat/api/salut/get/llistatPatologies
 */
if ($slug === "llistatPatologies") {

    $sql = <<<SQL
            SELECT c.id, c.patologia, c.genere,
            m.id AS idMedicament, m.medicament AS medicaments, m.dosis, m.necessita_recepta AS recepta, m.quantitat_defecte
            FROM %s AS c
            LEFT JOIN %s AS pm ON pm.patologia_id = c.id
            LEFT JOIN %s AS m ON pm.medicament_id = m.id
            ORDER BY c.patologia
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_SALUT_PATOLOGIES, $pdo),
        qi(Tables::DB_SALUT_PATOLOGIES_MEDICAMENTS, $pdo),
        qi(Tables::DB_SALUT_MEDICAMENTS, $pdo)
    );

    try {

        $rows = $db->getData($query, [], false);

        if (!$rows) {
            Response::error(
                MissatgesAPI::error('notFound'),
                ['Esdeveniment no trobat'],
                404
            );
            return;
        }

        // 1) Patologies (deduplicades per id, sense encara els medicaments)
        $patologies = [];
        $seen = [];

        foreach ($rows as $r) {
            $id = Uuid::toString($r['id']);

            if (isset($seen[$id])) continue;
            $seen[$id] = true;

            $patologies[] = [
                'id'          => $id,
                'patologia'   => $r['patologia'],
                'genere'      => $r['genere'],
                'medicaments' => [],
            ];
        }

        // 2) Indexar medicaments per patologia_id (una patologia pot tenir-ne diversos)
        $medicamentsByPatologia = [];

        foreach ($rows as $r) {
            if (empty($r['idMedicament'])) continue; // patologia sense medicaments associats

            $patologiaId = Uuid::toString($r['id']);

            $medicamentsByPatologia[$patologiaId][] = [
                'id'                => Uuid::toString($r['idMedicament']),
                'medicaments'       => $r['medicaments'],
                'dosis'             => $r['dosis'],
                'recepta'           => (bool) $r['recepta'],
                'quantitat_defecte' => $r['quantitat_defecte'],
            ];
        }

        // 3) Merge final
        foreach ($patologies as &$p) {
            $p['medicaments'] = $medicamentsByPatologia[$p['id']] ?? [];
        }
        unset($p);

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $patologies,
            httpCode: 200
        );
    } catch (Throwable $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }


    /**
     * GET : Patologia ID
     * URL: https://elliot.cat/api/salut/get/patologiaID?id=33333
     */
} else if ($slug === "patologiaID") {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        Response::error('Missing id', [], 400);
        exit;
    }

    $sql = <<<SQL
            SELECT c.id, c.patologia, c.genere,
            m.id AS idMedicament, m.medicament AS medicaments, m.dosis, m.necessita_recepta AS recepta, m.quantitat_defecte
            FROM %s AS c
            LEFT JOIN %s AS pm ON pm.patologia_id = c.id
            LEFT JOIN %s AS m ON pm.medicament_id = m.id
            WHERE c.id = :id
            ORDER BY c.patologia
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_SALUT_PATOLOGIES, $pdo),
        qi(Tables::DB_SALUT_PATOLOGIES_MEDICAMENTS, $pdo),
        qi(Tables::DB_SALUT_MEDICAMENTS, $pdo)
    );

    try {

        $params = [':id' => Uuid::toBinary($id)];
        $rows = $db->getData($query, $params, false);

        if (!$rows) {
            Response::error(
                MissatgesAPI::error('notFound'),
                ['Esdeveniment no trobat'],
                404
            );
            return;
        }

        // 1) Dades de la patologia = primera fila
        $first = $rows[0];

        $patologia = [
            'id'          => Uuid::toString($first['id']),
            'patologia'   => $first['patologia'],
            'genere'      => $first['genere'],
            'medicaments' => [],
        ];

        // 2) Medicaments associats (poden ser-hi diversos per culpa del LEFT JOIN)
        foreach ($rows as $r) {
            if (empty($r['idMedicament'])) continue; // patologia sense medicaments associats

            $patologia['medicaments'][] = [
                'id'                => Uuid::toString($r['idMedicament']),
                'medicaments'       => $r['medicaments'],
                'dosis'             => $r['dosis'],
                'recepta'           => (bool) $r['recepta'],
                'quantitat_defecte' => $r['quantitat_defecte'],
            ];
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $patologia,
            httpCode: 200
        );
    } catch (Throwable $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }


    /**
     * GET : Informació facultatiu per ID
     * URL: https://elliot.cat/api/salut/get/facultatiuId?id=22222222
     */
} else if ($slug === "facultatiuId") {
    $id = $_GET['id'];

    $sql = <<<SQL
                SELECT f.id, f.nom, f.direccio, f.ciutat_id, f.email, f.telefon, f.created_at, f.updated_at, f.especialitat, f.genere
                FROM %s AS f
                WHERE f.id = :id
                LIMIT 1;
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_SALUT_FACULTATIUS, $pdo)
    );

    try {

        $params = [':id' => Uuid::toBinary($id)];
        $result = $db->getData($query, $params, true);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
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
     * GET : Llistat facultatius
     * URL: https://elliot.cat/api/salut/get/llistatFacultatius
     */
} else if ($slug === "llistatFacultatius") {

    $sql = <<<SQL
                SELECT f.id, f.nom, f.direccio, f.ciutat_id, f.email, f.telefon, f.created_at, f.updated_at, f.especialitat,
                    COALESCE(c.ciutat_ca, c.ciutat) AS nomCiutat
                FROM %s AS f
                LEFT JOIN %s AS c ON c.id = f.ciutat_id
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_SALUT_FACULTATIUS, $pdo),
        qi(Tables::DB_CIUTATS, $pdo)
    );

    try {

        $result = $db->getData($query, [], false);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
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
     * GET : Llistat medicaments
     * URL: https://elliot.cat/api/salut/get/llistatMedicaments
     */
} else if ($slug === "llistatMedicaments") {

    $sql = <<<SQL
                SELECT m.id, m.medicament, m.dosis, m.necessita_recepta, m.quantitat_defecte, m.facultatiu_id, f.nom
                FROM %s AS m
                LEFT JOIN %s AS f ON m.facultatiu_id = f.id
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_SALUT_MEDICAMENTS, $pdo),
        qi(Tables::DB_SALUT_FACULTATIUS, $pdo)
    );

    try {

        $result = $db->getData($query, [], false);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
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
     * GET : Info Medicament ID
     * URL: https://elliot.cat/api/salut/get/medicamentId?id=2222
     */
} else if ($slug === "medicamentId") {

    $id = $_GET['id'];

    $sql = <<<SQL
                SELECT m.id, m.medicament, m.dosis, m.necessita_recepta, m.quantitat_defecte, m.facultatiu_id, m.created_at, m.updated_at
                FROM %s AS m
                WHERE m.id = :id
                LIMIT 1
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_SALUT_MEDICAMENTS, $pdo)
    );

    try {
        $params = [':id' => Uuid::toBinary($id)];
        $result = $db->getData($query, $params, true);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }
} else {
    // Slug no reconocido
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
