<?php

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
            $where[] = "b.categoria = UUID_TO_BIN(:cat)";
            $params[':cat'] = $cat;
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
                    b.categoria, BIN_TO_UUID(b.categoria) AS categoria_uuid, b.post_date, b.post_modified, t.tema_ca
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
    // URL: /api/blog/get/?articleSlug=revolut    
} else if (isset($_GET['articleSlug'])) {
    $slug = $_GET['articleSlug'];
    global $conn;

    $query = "SELECT b.id, b.post_type, b.post_title, b.post_excerpt, b.lang, b.post_content, b.post_status, b.slug, b.categoria, b.post_date, b.post_modified, t.tema_ca
        FROM db_blog AS b
        LEFT JOIN aux_temes AS t ON b.categoria = t.id
        WHERE b.slug = :slug";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);

    $stmt->execute();

    // Verificar si hay resultados antes de hacer fetch
    if ($stmt->rowCount() === 0) {
        echo json_encode(["error" => "No rows found"]);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Enviar respuesta en formato JSON
    echo json_encode($data);
} else {
    // No se proporcionó un token
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Access not allowed']);
    exit();
}
