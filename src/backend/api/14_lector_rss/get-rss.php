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
 * BACKEND RSS LECTOR
 * GET RSS
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
 * GET : Llistat de feeds RSS
 * URL: https://elliot.cat/api/rss/get/llistatFeeds
 */
if ($slug === "llistatFeeds") {

    $sql = <<<SQL
            SELECT
                f.id,
                f.nom,
                f.web,
                f.feed,
                f.tipus,
                f.sub_tema_id,
                f.ordre,
                s.sub_tema,
                t.tema
            FROM %s as f
            LEFT JOIN %s AS s ON f.sub_tema_id = s.id
            LEFT JOIN %s AS t ON s.tema_id = t.id
            WHERE f.actiu = 1
            ORDER BY f.nom
            SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_RSS_FEEDS, $pdo),
        qi(Tables::DB_SUBTEMES, $pdo),
        qi(Tables::DB_TEMES, $pdo)
    );

    try {

        $rows = $db->getData($query, [], false);

        if (!$rows) {
            Response::error(
                MissatgesAPI::error('notFound'),
                ['Feeds RSS no trobats'],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $rows,
            httpCode: 200
        );
    } catch (Throwable $e) {

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
