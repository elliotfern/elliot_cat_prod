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
            MissatgesAPI::success('get'),
            $result,
            200
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
            c1.ciutat_ca AS ciutatNaixement,
            c2.ciutat_ca AS ciutatDefuncio,

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
            MissatgesAPI::success('get'),
            $base,
            200
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

    // ruta GET => "/api/persones/get/?grupPersona={id}"
} else if (isset($_GET['grupPersona'])) {

    $id = $_GET['grupPersona'] ?? null;

    $idHex = str_replace('-', '', strtolower($id));
    if (!ctype_xdigit($idHex) || strlen($idHex) !== 32) {
        Response::error(MissatgesAPI::error('invalid_data'), ['id' => 'invalid_uuid'], 400);
        exit;
    }

    $idBytes = hex2bin($idHex);

    try {
        if (empty($id)) {
            Response::error(MissatgesAPI::error('bad_request'), ['id' => 'required'], 400);
            exit;
        }

        $db = new Database();

        $query = "SELECT LOWER(CONCAT_WS('-',
        SUBSTR(HEX(a.id), 1, 8),
        SUBSTR(HEX(a.id), 9, 4),
        SUBSTR(HEX(a.id), 13, 4),
        SUBSTR(HEX(a.id), 17, 4),
        SUBSTR(HEX(a.id), 21)
        )) AS id, a.grup_ca, a.grup_es, a.grup_en, a.grup_it, a.grup_fr
         FROM " . Tables::PERSONES_GRUPS . " AS a
         WHERE a.id = :id
         LIMIT 1";

        $params = [':id' => $idBytes];
        $result = $db->getData($query, $params);

        if (empty($result)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit;
        }

        Response::success(MissatgesAPI::success('get'), $result, 200);
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

    // ruta GET => "/api/persones/get/?grupPersones"
} else if (isset($_GET['grupPersones'])) {

    try {

        $db = new Database();

        $query = "SELECT LOWER(CONCAT_WS('-',
        SUBSTR(HEX(a.id), 1, 8),
        SUBSTR(HEX(a.id), 9, 4),
        SUBSTR(HEX(a.id), 13, 4),
        SUBSTR(HEX(a.id), 17, 4),
        SUBSTR(HEX(a.id), 21)
        )) AS id, a.grup_ca, a.grup_es, a.grup_en, a.grup_it, a.grup_fr
         FROM " . Tables::PERSONES_GRUPS . " AS a";

        $rows = $db->getData($query);

        // Sanititzar strings perquè json_encode no peti per UTF-8 malformat
        array_walk_recursive($rows, function (&$v) {
            if (!is_string($v)) return;

            // Quitar NULs (muy típicos si hubo UTF-32 / bytes raros)
            $v = str_replace("\0", '', $v);

            // Intentar normalizar a UTF-8 válido
            // 1) Si ya es UTF-8 válido, lo deja igual
            if (!mb_check_encoding($v, 'UTF-8')) {
                // 2) Intenta desde ISO-8859-1 (latin1) -> UTF-8 (común en legacy)
                $v2 = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $v);
                if ($v2 !== false) {
                    $v = $v2;
                } else {
                    // 3) Último recurso: limpia bytes inválidos asumiendo UTF-8
                    $v3 = @iconv('UTF-8', 'UTF-8//IGNORE', $v);
                    if ($v3 !== false) $v = $v3;
                }
            } else {
                // Aun siendo UTF-8 válido, limpia bytes raros si los hubiera
                $v2 = @iconv('UTF-8', 'UTF-8//IGNORE', $v);
                if ($v2 !== false) $v = $v2;
            }
        });

        if (empty($rows)) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            exit;
        }

        Response::success(MissatgesAPI::success('get'), $rows, 200);
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
