<?php

declare(strict_types=1);

use App\Config\Database;

$db  = new Database();
$pdo = $db->getPdo();

// ---------------- CONFIG ----------------
const ORIG_TABLE = 'epgylzqu_historia_web.xfr_posts';
const DEST_TABLE = 'epgylzqu_elliot_cat.db_blog';

// Marca de origen
const DEST_POST_TYPE = 'post_wp';

// Idioma destino (CA=1 según tu mapping)
const DEST_LANG = 1;

// ⚠️ IMPORTANTE: categoria BIN(16) NOT NULL en db_blog
// Pon aquí el HEX (32 chars) de la categoria/tema que quieras usar para imports WP
// ejemplo: 'AABBCCDDEEFF00112233445566778899'
const CATEGORIA_HEX = '0x0197acfe062670cf96e00f8ae5e17eac'; // <-- CAMBIA ESTO


// Límite / batch
$limit = 5000;
// ----------------------------------------

// Helpers
function slugify(string $s): string
{
    $s = trim(mb_strtolower($s));
    $s = preg_replace('~[^\pL\pN]+~u', '-', $s) ?? $s;
    $s = trim($s, '-');
    if ($s === '') $s = 'post';
    return mb_substr($s, 0, 200);
}

// 1) Leer origen

$sqlSrc = "
    SELECT
        ID,
        post_date,
        post_modified,
        post_content,
        post_title,
        post_excerpt,
        post_status,
        post_name,
        post_type
    FROM epgylzqu_historia_web.xfr_posts
    WHERE post_type = 'post'
";

$stmtSrc = $pdo->query($sqlSrc);
$rows = $stmtSrc->fetchAll(PDO::FETCH_ASSOC);

echo "Rows found: " . count($rows) . PHP_EOL;
print_r($rows);
exit;


// 2) Prepared: exists + insert
$sqlExists = "
    SELECT id
    FROM " . DEST_TABLE . "
    WHERE slug = :slug
      AND post_type = :post_type
      AND lang = :lang
    LIMIT 1
";
$stmtExists = $pdo->prepare($sqlExists);

$sqlIns = "
    INSERT INTO " . DEST_TABLE . "
      (post_type, post_title, post_content, post_excerpt, lang, post_status, slug, categoria, post_date, post_modified)
    VALUES
      (:post_type, :post_title, :post_content, :post_excerpt, :lang, :post_status, :slug, :categoria, :post_date, :post_modified)
";
$stmtIns = $pdo->prepare($sqlIns);

$inserted = 0;
$skipped  = 0;

foreach ($rows as $r) {
    $wpId = (int)$r['ID'];

    $slug = trim((string)($r['post_name'] ?? ''));
    if ($slug === '') {
        $slug = slugify((string)($r['post_title'] ?? ''));
        // si quieres hacerlo 100% único cuando post_title se repite:
        // $slug .= '-' . $wpId;
    }

    // Existe?
    $stmtExists->execute([
        ':slug' => $slug,
        ':post_type' => DEST_POST_TYPE,
        ':lang' => DEST_LANG,
    ]);
    $found = $stmtExists->fetch(PDO::FETCH_ASSOC);

    if ($found) {
        $skipped++;
        echo "SKIPPED slug={$slug}\n";
        continue;
    }

    // Mapeo status WP -> tu status
    $wpStatus = (string)($r['post_status'] ?? '');
    $destStatus = match ($wpStatus) {
        'publish' => 'publicat',
        'draft'   => 'esborrany',
        default   => $wpStatus !== '' ? $wpStatus : 'publicat'
    };

    // Excerpt nullable
    $excerpt = (string)($r['post_excerpt'] ?? '');
    $excerptOrNull = trim($excerpt) === '' ? null : $excerpt;

    // Fechas: tu db_blog permite 0000... pero mejor poner algo válido si viene vacío
    $postDate = (string)($r['post_date'] ?? '0000-00-00 00:00:00');
    $postMod  = (string)($r['post_modified'] ?? $postDate);

    // Insert
    $stmtIns->bindValue(':post_type', DEST_POST_TYPE, PDO::PARAM_STR);
    $stmtIns->bindValue(':post_title', (string)($r['post_title'] ?? ''), PDO::PARAM_STR);
    $stmtIns->bindValue(':post_content', (string)($r['post_content'] ?? ''), PDO::PARAM_STR);

    if ($excerptOrNull === null) $stmtIns->bindValue(':post_excerpt', null, PDO::PARAM_NULL);
    else $stmtIns->bindValue(':post_excerpt', $excerptOrNull, PDO::PARAM_STR);

    $stmtIns->bindValue(':lang', DEST_LANG, PDO::PARAM_INT);
    $stmtIns->bindValue(':post_status', $destStatus, PDO::PARAM_STR);
    $stmtIns->bindValue(':slug', $slug, PDO::PARAM_STR);

    // categoria BIN(16)
    $bin = @hex2bin(CATEGORIA_HEX);
    if ($bin === false || strlen($bin) !== 16) {
        throw new RuntimeException('CATEGORIA_HEX inválido: debe ser HEX de 32 chars (16 bytes).');
    }
    $stmtIns->bindValue(':categoria', $bin, PDO::PARAM_LOB);

    $stmtIns->bindValue(':post_date', $postDate, PDO::PARAM_STR);
    $stmtIns->bindValue(':post_modified', $postMod, PDO::PARAM_STR);

    $stmtIns->execute();

    $newId = (int)$pdo->lastInsertId();
    $inserted++;

    // Log opcional
    // echo "Inserted WP#$wpId -> db_blog#$newId slug=$slug\n";
}

echo "DONE. inserted={$inserted} skipped={$skipped}\n";
