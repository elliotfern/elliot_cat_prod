<?php

declare(strict_types=1);

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;

$slug = $routeParams[0] ?? null;
$db  = new Database();
$pdo = $db->getPdo();

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://elliot.cat");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Llistat complet del blog
// URL: /api/blog/get/llistatArticles?page=1&limit=10&order=asc|desc
if ($slug === 'llistatArticles') {
    $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $order = isset($_GET['order']) ? strtolower((string)$_GET['order']) : 'desc';

    $year  = isset($_GET['year']) ? (int)$_GET['year'] : 0;
    $cat   = isset($_GET['cat']) ? (string)$_GET['cat'] : ''; // puede venir vacío

    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 10;
    if ($limit > 50) $limit = 50;
    if (!in_array($order, ['asc', 'desc'], true)) $order = 'desc';

    $offset = ($page - 1) * $limit;

    // WHERE dinámico
    $where = [];
    $params = [];

    // (Opcional) filtra por año usando el campo post_date (YYYY-...)
    if ($year >= 1970 && $year <= 2100) {
        $where[] = "YEAR(b.post_date) = :year";
        $params[':year'] = $year;
    }

    // (Opcional) filtra por categoría
    // - si cat="0" => sin categoría
    // - si cat es UUID (texto) => convertimos a BINARY(16)
    if ($cat !== '') {
        if ($cat === '0') {
            $where[] = "b.categoria IS NULL";
        } else {
            $where[] = "b.categoria = UNHEX(:cat)";
            $params[':cat'] = $cat; // cat viene como HEX (32 chars)
        }
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    try {
        // COUNT total
        $sqlCount = sprintf(
            "SELECT COUNT(*) AS total FROM %s AS b %s",
            qi(Tables::BLOG, $pdo),
            $whereSql
        );
        $stmtCount = $pdo->prepare($sqlCount);
        foreach ($params as $k => $v) {
            $stmtCount->bindValue($k, $v);
        }
        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();

        // DATA paginada
        $sql = sprintf(
            "SELECT b.id, b.post_type, b.post_title, b.post_excerpt, b.lang, b.post_status, b.slug,
                    b.categoria, HEX(b.categoria) AS categoria_hex, b.post_date, b.post_modified, t.tema_ca
             FROM %s AS b
             LEFT JOIN %s AS t ON b.categoria = t.id
             %s
             ORDER BY b.post_date %s
             LIMIT :limit OFFSET :offset",
            qi(Tables::BLOG, $pdo),
            qi(Tables::DB_TEMES, $pdo),
            $whereSql,
            strtoupper($order)
        );

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pages = (int)ceil(($total > 0 ? $total : 1) / $limit);

        Response::success(
            MissatgesAPI::success('get'),
            [
                'items' => $rows,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => $pages,
                    'has_prev' => $page > 1,
                    'has_next' => $page < $pages,
                ],
                'filters' => [
                    'year' => $year ?: null,
                    'cat'  => $cat !== '' ? $cat : null,
                    'order' => $order,
                ],
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

    // Facets del blog (anys + categories)
    // URL: /api/blog/get/filtresArticles
} else if ($slug === 'filtresArticles') {

    // 1) Anyos disponibles (de post_date)
    $sqlYears = sprintf(
        "SELECT DISTINCT YEAR(b.post_date) AS y
         FROM %s AS b
         WHERE b.post_date IS NOT NULL
         ORDER BY y DESC",
        qi(Tables::BLOG, $pdo)
    );

    // 2) Categories disponibles (HEX id + label)
    // OJO: si quieres incluir también categorías que no estén usadas, cambia el JOIN.
    $sqlCats = sprintf(
        "SELECT DISTINCT
            HEX(t.id) AS hex,
            t.tema_ca AS label
         FROM %s AS b
         INNER JOIN %s AS t ON b.categoria = t.id
         WHERE b.categoria IS NOT NULL
         ORDER BY label ASC",
        qi(Tables::BLOG, $pdo),
        qi(Tables::DB_TEMES, $pdo)
    );

    try {
        // YEARS
        $stmtY = $pdo->query($sqlYears);
        $yearsRaw = $stmtY->fetchAll(PDO::FETCH_ASSOC);

        $years = [];
        foreach ($yearsRaw as $r) {
            $y = (int)($r['y'] ?? 0);
            if ($y > 0) $years[] = $y;
        }

        // CATEGORIES
        $stmtC = $pdo->query($sqlCats);
        $catsRaw = $stmtC->fetchAll(PDO::FETCH_ASSOC);

        $categories = [];
        foreach ($catsRaw as $r) {
            $hex = (string)($r['hex'] ?? '');
            $label = (string)($r['label'] ?? '');
            $hex = trim($hex);
            $label = trim($label);

            if ($hex === '' || $label === '') continue;

            $categories[] = [
                'hex' => $hex,
                'label' => $label,
            ];
        }

        Response::success(
            MissatgesAPI::success('get'),
            [
                'years' => $years,
                'categories' => $categories,
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

    // Article per slug
    // URL: /api/blog/get/articleSlug?articleSlug=revolut  
} else if ($slug === 'articleSlug') {

    header('Content-Type: application/json; charset=utf-8');

    $articleSlug = (string)($_GET['articleSlug'] ?? '');
    $articleSlug = trim($articleSlug);

    // Validació bàsica de slug (ajusta si uses altres caràcters)
    if ($articleSlug === '' || !preg_match('~^[a-z0-9][a-z0-9\-]*[a-z0-9]$|^[a-z0-9]$~', $articleSlug)) {
        Response::error('Paràmetre articleSlug invàlid', [], 400);
        return;
    }

    $sql = "
        SELECT
            b.id,
            b.post_type,
            b.post_title,
            b.post_excerpt,
            b.lang,
            b.post_content,
            b.post_status,
            b.slug,
            b.categoria,
            HEX(b.categoria) AS categoria_hex,
            b.post_date,
            b.post_modified,
            t.tema_ca
        FROM " . qi(Tables::BLOG, $pdo) . " AS b
        LEFT JOIN " . qi(Tables::DB_TEMES, $pdo) . " AS t ON b.categoria = t.id
        WHERE b.slug = :slug
        LIMIT 1
    ";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':slug', $articleSlug, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        // (Opcional) si quieres ocultar borradores:
        // if (($row['post_status'] ?? '') !== 'publish') { ... 404 o 403 ... }

        Response::success(
            MissatgesAPI::success('get'),
            $row,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Article per slug
    // URL: /api/blog/get/articleId?id=333  
} else if ($slug === 'articleId') {

    $idRaw = $_GET['id'] ?? null;
    $id = is_string($idRaw) ? (int)$idRaw : (int)$idRaw;

    if ($id <= 0) {
        Response::error('ID invàlid', ['id' => $idRaw], 400);
        exit;
    }

    try {
        // Nota: devolvemos categoria como HEX para que el frontend no trate binary(16) directamente
        $query = "
            SELECT
                b.id,
                b.post_type,
                b.post_title,
                b.post_content,
                b.post_excerpt,
                b.lang,
                b.post_status,
                b.slug,
                HEX(b.categoria) AS categoria,
                b.post_date,
                b.post_modified
            FROM db_blog b
            WHERE b.id = :id
            LIMIT 1
        ";

        $params = [':id' => $id];
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
            MissatgesAPI::success('get'),
            $result,
            200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }
} else {
    // No se proporcionó un token
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Access not allowed']);
    exit();
}
