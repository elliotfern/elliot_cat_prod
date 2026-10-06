<?php

declare(strict_types=1);

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Utils\Uuid;
use Ramsey\Uuid\Uuid as RamseyUuid;

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

function parseShortcodeAttrs(string $attrStr): array
{
    $attrs = [];
    $attrStr = html_entity_decode($attrStr, ENT_QUOTES, 'UTF-8');

    if (preg_match_all('~(\w+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s]+))~u', $attrStr, $m, PREG_SET_ORDER)) {
        foreach ($m as $x) {
            $k = strtolower($x[1]);
            $attrs[$k] = $x[2] !== '' ? $x[2] : (($x[3] ?? '') !== '' ? $x[3] : ($x[4] ?? ''));
        }
    }

    return $attrs;
}

function renderBlogImgShortcodes(string $html, PDO $pdo): string
{
    // Tipos permitidos en artículos públicos
    $allowedTypeIds = [1, 2, 3, 4, 6, 7, 8, 11, 12, 13, 15, 16, 17, 18, 19, 20, 21, 22, 23];

    if (!preg_match_all('~\[img\s+([^\]]+)\]~i', $html, $matches, PREG_SET_ORDER)) {
        return $html;
    }

    $items = [];
    $ids = []; // uuid string => true

    foreach ($matches as $m) {
        $attrs = parseShortcodeAttrs($m[1]);
        $uuid = strtolower(trim((string)($attrs['id'] ?? '')));

        if ($uuid !== '' && RamseyUuid::isValid($uuid)) {
            $items[] = ['raw' => $m[0], 'id' => $uuid, 'attrs' => $attrs];
            $ids[$uuid] = true;
        } else {
            $html = str_replace(
                $m[0],
                '<div class="alert alert-warning my-3">Imatge amb id invàlid</div>',
                $html
            );
        }
    }

    if (!$ids) {
        return $html;
    }

    $idList = array_keys($ids);
    $in = implode(',', array_fill(0, count($idList), '?'));

    $sql = "
        SELECT i.id, i.nameImg, i.extension, i.alt, i.typeImg, t.name AS dir
        FROM db_img i
        JOIN db_img_type t ON t.id = i.typeImg
        WHERE i.id IN ($in)
    ";

    $stmt = $pdo->prepare($sql);
    foreach ($idList as $k => $uuid) {
        $stmt->bindValue($k + 1, RamseyUuid::fromString($uuid)->getBytes(), PDO::PARAM_LOB);
    }
    $stmt->execute();

    $byId = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $byId[RamseyUuid::fromBytes($r['id'])->toString()] = $r;
    }

    foreach ($items as $it) {
        $uuid  = $it['id'];
        $attrs = $it['attrs'];

        if (!isset($byId[$uuid])) {
            $replacement = '<div class="alert alert-warning my-3">Imatge no trobada (id='
                . htmlspecialchars($uuid, ENT_QUOTES, 'UTF-8') . ')</div>';
            $html = str_replace($it['raw'], $replacement, $html);
            continue;
        }

        $img = $byId[$uuid];

        if (!in_array((int)$img['typeImg'], $allowedTypeIds, true)) {
            $replacement = '<div class="alert alert-warning my-3">Tipus d\'imatge no permès (id='
                . htmlspecialchars($uuid, ENT_QUOTES, 'UTF-8') . ')</div>';
            $html = str_replace($it['raw'], $replacement, $html);
            continue;
        }

        $ext = ltrim(trim((string)$img['extension']), '.');
        if ($ext === '') {
            $ext = 'jpg';
        }

        $src = 'https://media.elliot.cat/img/'
            . rawurlencode((string)$img['dir']) . '/'
            . rawurlencode((string)$img['nameImg']) . '.'
            . rawurlencode($ext);

        $alt = $attrs['alt'] ?? ($img['alt'] ?? '');
        $altSafe = htmlspecialchars((string)$alt, ENT_QUOTES, 'UTF-8');

        $classExtra = trim((string)($attrs['class'] ?? ''));
        $classSafe = htmlspecialchars(trim('img-fluid rounded ' . $classExtra), ENT_QUOTES, 'UTF-8');

        $caption = (string)($attrs['caption'] ?? '');
        $captionSafe = htmlspecialchars($caption, ENT_QUOTES, 'UTF-8');

        $figure =
            '<figure class="my-4 text-center">' .
            '<img loading="lazy" decoding="async" class="' . $classSafe . '" src="'
            . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . $altSafe . '">' .
            ($caption !== '' ? '<figcaption class="small text-muted mt-2">' . $captionSafe . '</figcaption>' : '') .
            '</figure>';

        $html = str_replace($it['raw'], $figure, $html);
    }

    return $html;
}

// Llistat complet del blog
// URL: /api/blog/get/llistatArticles?page=1&limit=10&order=asc|desc
if ($slug === 'llistatArticles') {

    $excludedPostType = 'historia_oberta';

    $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $order = isset($_GET['order']) ? strtolower((string)$_GET['order']) : 'desc';

    $year = isset($_GET['year']) ? (int)$_GET['year'] : 0;
    $cat  = isset($_GET['cat']) ? trim((string)$_GET['cat']) : '';

    $idiomaId = isset($_GET['idioma_id'])
        ? trim((string)$_GET['idioma_id'])
        : '';


    // =========================================================
    // VALIDACIÓ PARÀMETRES
    // =========================================================

    if ($page < 1) {
        $page = 1;
    }

    if ($limit < 1) {
        $limit = 10;
    }

    if ($limit > 50) {
        $limit = 50;
    }

    if (!in_array($order, ['asc', 'desc'], true)) {
        $order = 'desc';
    }

    $offset = ($page - 1) * $limit;


    // =========================================================
    // WHERE DINÀMIC
    // =========================================================

    $where = [];
    $params = [];

    // Excloure historia_oberta
    $where[] = "b.post_type <> :excluded_post_type";
    $params[':excluded_post_type'] = $excludedPostType;


    // =========================================================
    // FILTRE ANY
    // =========================================================

    if ($year >= 1970 && $year <= 2100) {

        $where[] = "YEAR(b.post_date) = :year";
        $params[':year'] = $year;
    }


    // =========================================================
    // FILTRE CATEGORIA
    // =========================================================

    // cat="0" => sense categoria
    // cat=UUID => categoria concreta
    if ($cat !== '') {

        if ($cat === '0') {

            $where[] = "b.categoria_id IS NULL";
        } else {

            if (!Uuid::isValid($cat)) {
                Response::error(
                    'Paràmetre cat invàlid',
                    [],
                    400
                );
                return;
            }

            $where[] = "b.categoria_id = :cat";
            $params[':cat'] = Uuid::toBinary($cat);
        }
    }


    // =========================================================
    // FILTRE IDIOMA
    // =========================================================

    if ($idiomaId !== '') {

        if (!Uuid::isValid($idiomaId)) {

            Response::error(
                'Paràmetre idioma_id invàlid',
                [],
                400
            );
            return;
        }

        $where[] = "b.idioma_id = :idioma_id";
        $params[':idioma_id'] = Uuid::toBinary($idiomaId);
    }


    $whereSql = $where
        ? 'WHERE ' . implode(' AND ', $where)
        : '';


    try {

        // =========================================================
        // COUNT TOTAL
        // =========================================================

        $sqlCount = sprintf(
            "SELECT COUNT(*) AS total
             FROM %s AS b
             %s",
            qi(Tables::BLOG, $pdo),
            $whereSql
        );

        $stmtCount = $pdo->prepare($sqlCount);

        foreach ($params as $key => $value) {
            $stmtCount->bindValue($key, $value);
        }

        $stmtCount->execute();

        $total = (int)$stmtCount->fetchColumn();


        // =========================================================
        // DATA PAGINADA
        // =========================================================

        $sql = sprintf(
            "SELECT
                b.id,
                b.post_type,
                b.post_title,
                b.post_excerpt,
                b.idioma_id,
                b.post_status,
                b.slug,
                b.categoria_id,
                b.post_date,
                b.post_modified,
                t.tema
             FROM %s AS b
             LEFT JOIN %s AS t
                ON b.categoria_id = t.id
             %s
             ORDER BY b.post_date %s
             LIMIT :limit OFFSET :offset",
            qi(Tables::BLOG, $pdo),
            qi(Tables::DB_TEMES, $pdo),
            $whereSql,
            strtoupper($order)
        );

        $stmt = $pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(
            ':limit',
            $limit,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $rowsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // =========================================================
        // CONVERTIR BINARY(16) → UUID
        // =========================================================

        $rows = [];

        foreach ($rowsRaw as $row) {

            if (
                isset($row['id']) &&
                is_string($row['id']) &&
                strlen($row['id']) === 16
            ) {
                $row['id'] = Uuid::toString($row['id']);
            }

            if (
                isset($row['idioma_id']) &&
                is_string($row['idioma_id']) &&
                strlen($row['idioma_id']) === 16
            ) {
                $row['idioma_id'] = Uuid::toString($row['idioma_id']);
            }

            if (
                isset($row['categoria_id']) &&
                is_string($row['categoria_id']) &&
                strlen($row['categoria_id']) === 16
            ) {
                $row['categoria_id'] = Uuid::toString($row['categoria_id']);
            }

            $rows[] = $row;
        }


        // =========================================================
        // PAGINACIÓ
        // =========================================================

        $pages = (int)ceil(
            ($total > 0 ? $total : 1) / $limit
        );


        // =========================================================
        // RESPONSE
        // =========================================================

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
                    'cat' => $cat !== '' ? $cat : null,
                    'order' => $order,
                    'idioma_id' => $idiomaId !== ''
                        ? $idiomaId
                        : null,
                ],
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

    // Facets del blog (anys + categories)
    // URL: /api/blog/get/filtresArticles
} else if ($slug === 'filtresArticles') {

    $excludedPostType = 'historia_oberta';

    // Solo artículos publicados
    $statusWhere = " AND b.post_status IN ('publish', 'published', 'publicat')";

    // 1) Años disponibles (post_date)
    $sqlYears = sprintf(
        "SELECT DISTINCT
            YEAR(b.post_date) AS y
         FROM %s AS b
         WHERE b.post_date IS NOT NULL
           AND b.post_type <> :excluded_post_type
           %s
         ORDER BY y DESC",
        qi(Tables::BLOG, $pdo),
        $statusWhere
    );

    // 2) Categorías disponibles
    $sqlCats = sprintf(
        "SELECT DISTINCT
            t.id AS id,
            t.tema AS label
         FROM %s AS b
         INNER JOIN %s AS t ON b.categoria_id = t.id
         WHERE b.categoria_id IS NOT NULL
           AND b.post_date IS NOT NULL
           AND b.post_type <> :excluded_post_type
           %s
         ORDER BY label ASC",
        qi(Tables::BLOG, $pdo),
        qi(Tables::DB_TEMES, $pdo),
        $statusWhere
    );

    // 3) Idiomas disponibles
    // Se filtran por el código/valor del idioma, no por el UUID.
    $allowedLangs = [
        'Català',
        'Castellà',
        'Anglès',
        'Francès',
        'Italià',
    ];

    $langPlaceholders = [];

    foreach ($allowedLangs as $i => $_) {
        $langPlaceholders[] = ':idioma' . $i;
    }

    $inLang = implode(',', $langPlaceholders);

    $sqlLangs = sprintf(
        "SELECT DISTINCT
            l.id AS id,
            l.idioma AS label
         FROM %s AS b
         INNER JOIN %s AS l ON b.idioma_id = l.id
         WHERE l.idioma IN ($inLang)
           AND b.post_date IS NOT NULL
           AND b.post_type <> :excluded_post_type
           %s
         ORDER BY label ASC",
        qi(Tables::BLOG, $pdo),
        qi(Tables::DB_IDIOMES, $pdo),
        $statusWhere
    );

    try {

        // =========================================================
        // YEARS
        // =========================================================

        $stmtY = $pdo->prepare($sqlYears);
        $stmtY->bindValue(':excluded_post_type', $excludedPostType);
        $stmtY->execute();

        $yearsRaw = $stmtY->fetchAll(PDO::FETCH_ASSOC);

        $years = [];

        foreach ($yearsRaw as $r) {
            $y = (int)($r['y'] ?? 0);

            if ($y > 0) {
                $years[] = $y;
            }
        }

        $years = array_values(array_unique($years));
        rsort($years);


        // =========================================================
        // CATEGORIES
        // =========================================================

        $stmtC = $pdo->prepare($sqlCats);
        $stmtC->bindValue(':excluded_post_type', $excludedPostType);
        $stmtC->execute();

        $catsRaw = $stmtC->fetchAll(PDO::FETCH_ASSOC);

        $categories = [];

        foreach ($catsRaw as $r) {

            $idBinary = $r['id'] ?? null;
            $label = trim((string)($r['label'] ?? ''));

            if (!is_string($idBinary) || strlen($idBinary) !== 16 || $label === '') {
                continue;
            }

            $categories[] = [
                'id' => Uuid::toString($idBinary),
                'label' => $label,
            ];
        }

        usort(
            $categories,
            fn($a, $b) => strcmp($a['label'], $b['label'])
        );


        // =========================================================
        // LANGS
        // =========================================================

        $stmtL = $pdo->prepare($sqlLangs);

        foreach ($allowedLangs as $i => $lang) {
            $stmtL->bindValue(
                ':idioma' . $i,
                $lang,
                PDO::PARAM_STR
            );
        }

        $stmtL->bindValue(
            ':excluded_post_type',
            $excludedPostType
        );

        $stmtL->execute();

        $langsRaw = $stmtL->fetchAll(PDO::FETCH_ASSOC);

        $langs = [];

        foreach ($langsRaw as $r) {

            $idBinary = $r['id'] ?? null;
            $label = trim((string)($r['label'] ?? ''));

            if (!is_string($idBinary) || strlen($idBinary) !== 16 || $label === '') {
                continue;
            }

            $langs[] = [
                'id' => Uuid::toString($idBinary),
                'label' => $label,
            ];
        }

        usort(
            $langs,
            fn($a, $b) => strcmp($a['label'], $b['label'])
        );


        // =========================================================
        // RESPONSE
        // =========================================================

        Response::success(
            MissatgesAPI::success('get'),
            [
                'years' => $years,
                'categories' => $categories,
                'langs' => $langs,
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
            b.idioma_id,
            b.post_content,
            b.post_status,
            b.slug,
            b.categoria_id,
            b.post_date,
            b.post_modified,
            t.tema
        FROM " . qi(Tables::BLOG, $pdo) . " AS b
        LEFT JOIN " . qi(Tables::DB_TEMES, $pdo) . " AS t ON b.categoria_id = t.id
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
        $hex = (string)($row['categoria_id'] ?? '');
        $row['categoria_id'] = $hex !== '' ? ($hex) : null;

        // ✅ Reemplazar shortcodes de imágenes del blog
        if (isset($row['post_content']) && is_string($row['post_content']) && $row['post_content'] !== '') {
            $row['post_content'] = renderBlogImgShortcodes($row['post_content'], $pdo);
        }

        Response::success(
            MissatgesAPI::success('get'),
            $row,
            httpCode: 200
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

    $id = $_GET['id'] ?? null;

    try {
        // Nota: devolvemos categoria como HEX para que el frontend no trate binary(16) directamente
        $query = "
            SELECT
                b.id,
                b.post_type,
                b.post_title,
                b.post_content,
                b.post_excerpt,
                b.idioma_id,
                b.post_status,
                b.slug,
                b.categoria_id,
                b.post_date,
                b.post_modified
            FROM db_blog b
            WHERE b.id = :id
            LIMIT 1
        ";

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

        // ✅ Normalizar categoria para que coincida con el endpoint de categorías (UUID con guiones)
        $hex = (string)($result['categoria_id'] ?? '');


        Response::success(
            MissatgesAPI::success('get'),
            $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Llistat Historia Oberta (INTRANET)
    // URL: /api/blog/get/llistatHistoriaOberta?page=1&limit=10&order=asc|desc&curs=0|id&idioma_id=0|id&status=publish|draft|...
} else if ($slug === 'llistatHistoriaOberta') {

    $sql = "SELECT
                b.id,
                c.ordre AS curs_ordre,
                c.curs,
                b.idioma_id,
                b.post_status,
                b.slug,
                b.post_title,
                b.post_date,
                b.post_modified,
                 hoa.id AS grup_id,
                hoa.curs_id,
                hoa.ordre AS article_ordre,
                i.idioma
            FROM " . qi(Tables::BLOG, $pdo) . " AS b
            LEFT JOIN " . qi(Tables::DB_HISTORIA_OBERTA_ARTICLES, $pdo) . " AS hoa
                ON (
                    b.id = hoa.article_ca_id
                    OR b.id = hoa.article_es_id
                    OR b.id = hoa.article_en_id
                    OR b.id = hoa.article_fr_id
                    OR b.id = hoa.article_it_id
                )
            LEFT JOIN " . qi(Tables::DB_HISTORIA_OBERTA_CURSOS, $pdo) . " AS c ON c.id = hoa.curs_id
            LEFT JOIN " . qi(Tables::DB_IDIOMES, $pdo) . " AS i ON b.idioma_id = i.id
            WHERE b.post_type = 'historia_oberta'
            ORDER BY b.post_date DESC";

    try {

        $rows = $db->getData($sql, [], false) ?? [];

        Response::success(
            MissatgesAPI::success('get'),
            $rows,
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
    // No se proporcionó un token
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Access not allowed']);
    exit();
}
