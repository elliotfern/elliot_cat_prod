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

/**
 * Parse attrs tipo: id=22 alt="hola" class="rounded" caption="..."
 */
function parseShortcodeAttrs(string $attrStr): array
{
    $attrs = [];
    if (preg_match_all('~(\w+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s]+))~u', $attrStr, $m, PREG_SET_ORDER)) {
        foreach ($m as $x) {
            $k = strtolower($x[1]);
            $v = $x[2] !== '' ? $x[2] : ($x[3] !== '' ? $x[3] : $x[4]);
            $attrs[$k] = $v;
        }
    }
    return $attrs;
}

/**
 * Reemplaza [img id=22 ...] por <figure><img ...></figure>
 * - Permite imágenes de cualquier type
 * - Una sola query para todas las ids
 */
function renderBlogImgShortcodes(string $html, PDO $pdo): string
{
    // Tipos permitidos en artículos públicos
    $allowedTypeIds = [1, 2, 3, 4, 6, 7, 8, 11, 12, 13, 15, 16, 17];

    // Buscar shortcodes [img ...]
    if (!preg_match_all('~\[img\s+([^\]]+)\]~i', $html, $matches, PREG_SET_ORDER)) {
        return $html;
    }

    $items = [];
    $ids = [];

    foreach ($matches as $m) {
        $raw = $m[0];
        $attrStr = $m[1];

        $attrs = parseShortcodeAttrs($attrStr);
        $id = isset($attrs['id']) ? (int)$attrs['id'] : 0;

        if ($id > 0) {
            $items[] = ['raw' => $raw, 'id' => $id, 'attrs' => $attrs];
            $ids[$id] = true;
        }
    }

    if (!$ids) return $html;

    $idList = array_keys($ids);
    $in = implode(',', array_fill(0, count($idList), '?'));

    // Query única
    $sql = "
        SELECT i.id, i.nameImg, i.alt, i.typeImg, t.name AS dir
        FROM db_img i
        JOIN db_img_type t ON t.id = i.typeImg
        WHERE i.id IN ($in)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($idList);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $byId = [];
    foreach ($rows as $r) {
        $byId[(int)$r['id']] = $r;
    }

    foreach ($items as $it) {
        $id = $it['id'];
        $attrs = $it['attrs'];

        if (!isset($byId[$id])) {
            $replacement = '<div class="alert alert-warning my-3">Imatge no trobada (id=' . $id . ')</div>';
            $html = str_replace($it['raw'], $replacement, $html);
            continue;
        }

        $img = $byId[$id];

        // 🔐 Validación de tipo permitido
        if (!in_array((int)$img['typeImg'], $allowedTypeIds, true)) {
            $replacement = '<div class="alert alert-warning my-3">Tipus d\'imatge no permès (id=' . $id . ')</div>';
            $html = str_replace($it['raw'], $replacement, $html);
            continue;
        }

        $dir = (string)$img['dir']; // nombre del directorio
        $fileBase = (string)$img['nameImg'];

        $src = 'https://media.elliot.cat/img/' .
            rawurlencode($dir) . '/' .
            rawurlencode($fileBase) . '.jpg';

        // ALT prioridad: shortcode > BD > vacío
        $alt = $attrs['alt'] ?? ($img['alt'] ?? '');
        $altSafe = htmlspecialchars((string)$alt, ENT_QUOTES, 'UTF-8');

        // Clase extra opcional
        $classExtra = trim((string)($attrs['class'] ?? ''));
        $class = trim('img-fluid rounded ' . $classExtra);
        $classSafe = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');

        // Caption opcional
        $caption = (string)($attrs['caption'] ?? '');
        $captionSafe = htmlspecialchars($caption, ENT_QUOTES, 'UTF-8');

        $figure =
            '<figure class="my-4 text-center">' .
            '<img loading="lazy" decoding="async" class="' . $classSafe . '" src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . $altSafe . '">' .
            ($caption !== '' ? '<figcaption class="small text-muted mt-2">' . $captionSafe . '</figcaption>' : '') .
            '</figure>';

        $html = str_replace($it['raw'], $figure, $html);
    }

    return $html;
}

// Llistat complet del blog
// URL: /api/blog/get/llistatArticles?page=1&limit=10&order=asc|desc
if ($slug === 'llistatArticles') {
    $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $order = isset($_GET['order']) ? strtolower((string)$_GET['order']) : 'desc';

    $year  = isset($_GET['year']) ? (int)$_GET['year'] : 0;
    $cat   = isset($_GET['cat']) ? (string)$_GET['cat'] : ''; // puede venir vacío

    $lang = isset($_GET['lang']) ? (int)$_GET['lang'] : 0;

    // Idiomes permesos (IDs)
    $allowedLangIds = [1, 2, 3, 4, 7];
    if ($lang !== 0 && !in_array($lang, $allowedLangIds, true)) {
        $lang = 0;
    }

    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 10;
    if ($limit > 50) $limit = 50;
    if (!in_array($order, ['asc', 'desc'], true)) $order = 'desc';

    $offset = ($page - 1) * $limit;

    // WHERE dinámico
    $where = [];
    $params = [];

    // Excluir historia_oberta del listado del blog
    $where[] = "b.post_type <> :excluded_post_type";
    $params[':excluded_post_type'] = 'historia_oberta';

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

    // (Opcional) filtra por idioma (ID)
    if ($lang > 0) {
        $where[] = "b.lang = :lang";
        $params[':lang'] = $lang;
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
            "SELECT b.id, b.post_type, b.post_title, b.post_excerpt, b.lang, b.post_status, b.slug, HEX(b.categoria) AS categoria_hex, b.post_date, b.post_modified, t.tema_ca
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
                    'lang' => $lang > 0 ? $lang : null,
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

    // 3) Idiomes disponibles (només els permesos i que apareixen al blog)
    $allowedLangIds = [1, 2, 3, 4, 7];

    $inLang = implode(',', array_fill(0, count($allowedLangIds), '?'));

    $sqlLangs = sprintf(
        "SELECT DISTINCT
            l.id AS id,
            l.idioma_ca AS label
         FROM %s AS b
         INNER JOIN %s AS l ON b.lang = l.id
         WHERE b.lang IN ($inLang)
         ORDER BY label ASC",
        qi(Tables::BLOG, $pdo),
        qi(Tables::DB_IDIOMES, $pdo)
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

        // LANGS
        $stmtL = $pdo->prepare($sqlLangs);
        foreach ($allowedLangIds as $i => $langId) {
            $stmtL->bindValue($i + 1, $langId, PDO::PARAM_INT); // placeholders ? (1-based)
        }
        $stmtL->execute();
        $langsRaw = $stmtL->fetchAll(PDO::FETCH_ASSOC);

        $langs = [];
        foreach ($langsRaw as $r) {
            $id = (int)($r['id'] ?? 0);
            $label = trim((string)($r['label'] ?? ''));

            if ($id <= 0 || $label === '') continue;

            $langs[] = [
                'id' => $id,
                'label' => $label,
            ];
        }

        Response::success(
            MissatgesAPI::success('get'),
            [
                'years' => $years,
                'categories' => $categories,
                'langs' => $langs,
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
    // URL: /api/blog/get/articleSlug?articleSlug=revolut&scope=blog|historia
} else if ($slug === 'articleSlug') {

    header('Content-Type: application/json; charset=utf-8');

    $articleSlug = (string)($_GET['articleSlug'] ?? '');
    $articleSlug = trim($articleSlug);

    // scope: blog (default) o historia
    $scope = strtolower(trim((string)($_GET['scope'] ?? 'blog')));
    if (!in_array($scope, ['blog', 'historia'], true)) {
        $scope = 'blog';
    }

    // Validació bàsica de slug
    if ($articleSlug === '' || !preg_match('~^[a-z0-9][a-z0-9\-]*[a-z0-9]$|^[a-z0-9]$~', $articleSlug)) {
        Response::error('Paràmetre articleSlug invàlid', [], 400);
        return;
    }

    // Segons l’scope:
    // - blog: NO deixar veure historia_oberta
    // - historia: només deixar veure historia_oberta
    $whereExtra = '';
    $bindPostType = false;

    if ($scope === 'blog') {
        $whereExtra = " AND b.post_type <> :post_type ";
        $bindPostType = true;
    } else if ($scope === 'historia') {
        $whereExtra = " AND b.post_type = :post_type ";
        $bindPostType = true;
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
            HEX(b.categoria) AS categoria_hex,
            b.post_date,
            b.post_modified,
            t.tema_ca
        FROM " . qi(Tables::BLOG, $pdo) . " AS b
        LEFT JOIN " . qi(Tables::DB_TEMES, $pdo) . " AS t ON b.categoria = t.id
        WHERE b.slug = :slug
        $whereExtra
        LIMIT 1
    ";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':slug', $articleSlug, PDO::PARAM_STR);

        if ($bindPostType) {
            $stmt->bindValue(':post_type', 'historia_oberta', PDO::PARAM_STR);
        }

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si existe slug pero no encaja con scope => 404 igual (así “no se puede abrir” desde esa sección)
        if (!$row) {
            Response::error(MissatgesAPI::error('not_found'), [], 404);
            return;
        }

        // ✅ Normalizar categoria (UUID con guiones)
        $hex = (string)($row['categoria_hex'] ?? '');
        $row['categoria'] = $hex !== '' ? hexToUuidText($hex) : null;

        // ✅ Reemplazar shortcodes de imágenes del blog
        if (isset($row['post_content']) && is_string($row['post_content']) && $row['post_content'] !== '') {
            $row['post_content'] = renderBlogImgShortcodes($row['post_content'], $pdo);
        }

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

        // ✅ Normalizar categoria para que coincida con el endpoint de categorías (UUID con guiones)
        $hex = (string)($result['categoria'] ?? '');
        $result['categoria'] = $hex !== '' ? hexToUuidText($hex) : null;

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
