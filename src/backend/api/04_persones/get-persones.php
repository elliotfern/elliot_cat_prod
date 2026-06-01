<?php

use App\Config\Database;
use App\Utils\Tables;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Uuid;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$db = new Database();
$pdo = $db->getPdo();

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
// Check if the request method is GET

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// 1) Base de dades persones: Llistat complet
// ruta GET => "https://elliot.cat/api/persones/get/llistatPersones"
if ($slug === 'llistatPersones') {

    $tableA   = qi(Tables::PERSONES, $pdo);
    $tableC   = qi(Tables::GEO_PAISOS, $pdo);
    $tableI   = qi(Tables::IMG, $pdo);
    $tableRel = qi(Tables::PERSONES_GRUPS_RELACIONS, $pdo);
    $tableG   = qi(Tables::PERSONES_GRUPS, $pdo);

    $sql = <<<SQL
            SELECT 
                a.id,
                a.nom,
                a.cognoms,
                a.slug,
                a.dia_naixement,
                a.mes_naixement,
                a.any_naixement,
                a.dia_defuncio,
                a.mes_defuncio,
                a.any_defuncio,
                c.pais_ca,
                i.nameImg,
                COALESCE(
                    JSON_ARRAYAGG(g.grup_ca),
                    JSON_ARRAY()
                ) AS grup
            FROM {$tableA} AS a
            LEFT JOIN {$tableC} AS c ON a.pais_autor_id = c.id
            LEFT JOIN {$tableI} AS i ON a.img_id = i.id
            LEFT JOIN {$tableRel} AS rel ON a.id = rel.persona_id
            LEFT JOIN {$tableG} AS g ON rel.grup_id = g.id
            GROUP BY a.id
            ORDER BY a.cognoms
            SQL;

    try {
        $result = $db->getData($sql);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            exit;
        }

        foreach ($result as &$row) {

            // 1. decode JSON grup (si DB lo devuelve como string)
            if (is_string($row['grup'])) {
                $decoded = json_decode($row['grup'], true);
                $row['grup'] = is_array($decoded) ? $decoded : [];
            }

            // 2. normalizar nulls
            $row['any_naixement'] = $row['any_naixement'] ?? null;
            $row['any_defuncio']  = $row['any_defuncio'] ?? null;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (\Throwable $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
        exit;
    }

    // ruta GET => "/api/persones/get/persona?slug=josep-fontana"
} else if ($slug === 'persona') {

    $autorSlug = $_GET['slug'] ?? null;

    try {
        if (empty($autorSlug)) {
            Response::error(MissatgesAPI::error('bad_request'), ['persona' => 'required'], 400);
            exit;
        }

        $db = new Database();

        // 🔥 IMPORTANTE: quitamos GROUP BY, quitamos GROUP_CONCAT
        // y dejamos que salgan filas reales (1 por grupo)
        $query = "
        SELECT 
            a.id,
            a.cognoms,
            a.nom,
            p.pais_ca,
            a.any_naixement,
            a.any_defuncio,
            i.nameImg,
            i.alt,
            a.web,
            a.created_at,
            a.updated_at,
            a.descripcio,
            a.slug,
            a.img_id,
            a.sexe_id,
            a.mes_naixement,
            a.dia_naixement,
            a.mes_defuncio,
            a.dia_defuncio,
            COALESCE(NULLIF(c1.ciutat_ca, ''), c1.ciutat) AS ciutatNaixement,
            COALESCE(NULLIF(c2.ciutat_ca, ''), c2.ciutat) AS ciutatDefuncio,
            g.id AS grup_id,
            g.grup_ca AS grup_nom,

            a.pais_autor_id,
            a.ciutat_defuncio_id,
            a.ciutat_naixement_id

        FROM " . Tables::PERSONES . " AS a
        LEFT JOIN " . Tables::GEO_PAISOS . " AS p ON a.pais_autor_id = p.id
        LEFT JOIN " . Tables::IMG . " AS i ON a.img_id = i.id
        LEFT JOIN " . Tables::GEO_CIUTATS . " AS c1 ON a.ciutat_naixement_id = c1.id
        LEFT JOIN " . Tables::GEO_CIUTATS . " AS c2 ON a.ciutat_defuncio_id = c2.id
        LEFT JOIN " . Tables::PERSONES_GRUPS_RELACIONS . " AS rel ON a.id = rel.persona_id
        LEFT JOIN " . Tables::PERSONES_GRUPS . " AS g ON rel.grup_id = g.id
        WHERE a.slug = :slug";

        $params = [':slug' => $autorSlug];

        $rows = $db->getData($query, $params);

        if (empty($rows)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit;
        }

        // 🔵 base persona (solo una vez)
        $base = $rows[0];

        // 🔥 reconstrucción correcta de grupos (sin GROUP_CONCAT)
        $grups = [];

        foreach ($rows as $r) {

            if (!empty($r['grup_id'])) {

                $grups[$r['grup_id']] = [
                    'id' => Uuid::toString($r['grup_id']),
                    'nom' => $r['grup_nom']
                ];
            }
        }

        // reindex limpio
        $base['grups'] = array_values($grups);

        // limpiar campos sueltos
        unset($base['grup_id'], $base['grup_nom']);

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $base,
            httpCode: 200
        );
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Internal error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        exit;
    }

    // ruta GET => "/api/persones/get/grupPersona?id={id}"
} else if ($slug === 'grupPersona') {

    $id = $_GET['id'] ?? null;

    try {
        if (empty($id)) {
            Response::error(MissatgesAPI::error('bad_request'), ['id' => 'required'], 400);
            exit;
        }

        $db = new Database();

        $query = "SELECT id, a.grup_ca, a.grup_es, a.grup_en, a.grup_it, a.grup_fr
         FROM " . Tables::PERSONES_GRUPS . " AS a
         WHERE a.id = :id
         LIMIT 1";

        $params = [':id' => uuid::toBinary($id)];
        $result = $db->getData($query, $params);

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Internal error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        exit;
    }

    // ruta GET => "/api/persones/get/grupPersones"
} else if ($slug === 'grupPersones') {

    try {

        $db = new Database();

        $query = "SELECT a.id, a.grup_ca
         FROM " . Tables::PERSONES_GRUPS . " AS a";

        $rows = $db->getData($query);

        if (empty($rows)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $rows,
            httpCode: 200
        );
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Internal error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        exit;
    }
} else {
    // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
