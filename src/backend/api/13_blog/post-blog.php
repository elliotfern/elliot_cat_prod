<?php

declare(strict_types=1);

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Config\Tables;
use App\Config\Audit;
use App\Utils\ValidacioErrors;
use App\Config\DatabaseConnection;

header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$conn = DatabaseConnection::getConnection();
if (!$conn) {
    Response::error(MissatgesAPI::error('errorBD'), ['No se pudo establecer conexión a la base de datos.'], 500);
}

if (!isAuthenticatedAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autoritzat (admin requerit)']);
    exit;
}

$userUuid = getAuthenticatedUserUuid(); // auditoría (si aplica)

// 📨 Entrada JSON
$inputData = file_get_contents('php://input');
$data = json_decode($inputData, true) ?: [];

$errors = [];

// 📥 Campos (post_date y post_modified se ignoran: backend los gestiona)
$post_type    = isset($data['post_type']) ? trim((string)$data['post_type']) : 'post';
$post_title   = isset($data['post_title']) ? trim((string)$data['post_title']) : '';
$post_content = isset($data['post_content']) ? (string)$data['post_content'] : '';
$post_excerpt = array_key_exists('post_excerpt', $data) ? trim((string)($data['post_excerpt'] ?? '')) : null;

$lang         = isset($data['lang']) ? (int)$data['lang'] : null;
$post_status  = isset($data['post_status']) ? trim((string)$data['post_status']) : 'publish';
$slug         = isset($data['slug']) ? trim((string)$data['slug']) : '';

$categoriaHex = isset($data['categoria']) ? strtoupper(trim((string)$data['categoria'])) : '';

// 🔎 Validaciones
if ($post_title === '') $errors[] = ValidacioErrors::requerit('post_title');
if ($post_content === '') $errors[] = ValidacioErrors::requerit('post_content');

if ($lang === null) {
    $errors[] = ValidacioErrors::requerit('lang');
} elseif ($lang < 0) {
    $errors[] = ValidacioErrors::format('lang', 'int_positiu');
}

if ($slug === '') {
    $errors[] = ValidacioErrors::requerit('slug');
} else {
    // slug simple
    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
        $errors[] = ValidacioErrors::format('slug', 'slug');
    }
    if (mb_strlen($slug) > 200) $errors[] = ValidacioErrors::massaLlarg('slug', 200);
}

if ($categoriaHex === '') {
    $errors[] = ValidacioErrors::requerit('categoria');
} elseif (!preg_match('/^[0-9A-F]{32}$/', $categoriaHex)) {
    $errors[] = ValidacioErrors::format('categoria', 'hex32');
}

if ($post_type !== '' && mb_strlen($post_type) > 20) $errors[] = ValidacioErrors::massaLlarg('post_type', 20);
if ($post_status !== '' && mb_strlen($post_status) > 20) $errors[] = ValidacioErrors::massaLlarg('post_status', 20);

if (!empty($errors)) {
    Response::error(MissatgesAPI::error('validacio'), $errors, 400);
}

try {
    // ✅ (Opcional) comprobar slug duplicado antes, para devolver mensaje bonito
    $checkSlug = $conn->prepare("SELECT 1 FROM db_blog WHERE slug = :slug LIMIT 1");
    $checkSlug->bindValue(':slug', $slug, PDO::PARAM_STR);
    $checkSlug->execute();
    if ($checkSlug->fetchColumn()) {
        Response::error(MissatgesAPI::error('duplicat'), [ValidacioErrors::duplicat('slug')], 409);
    }

    $sql = "
    INSERT INTO db_blog (
      post_type, post_title, post_content, post_excerpt, lang, post_status, slug, categoria, post_date, post_modified
    ) VALUES (
      :post_type, :post_title, :post_content, :post_excerpt, :lang, :post_status, :slug, UNHEX(:categoria), NOW(), NOW()
    )
  ";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':post_type', $post_type, PDO::PARAM_STR);
    $stmt->bindValue(':post_title', $post_title, PDO::PARAM_STR);
    $stmt->bindValue(':post_content', $post_content, PDO::PARAM_STR);

    // excerpt nullable
    $excerptVal = ($post_excerpt !== null && $post_excerpt !== '') ? $post_excerpt : null;
    $stmt->bindValue(':post_excerpt', $excerptVal, $excerptVal === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

    $stmt->bindValue(':lang', $lang, PDO::PARAM_INT);
    $stmt->bindValue(':post_status', $post_status, PDO::PARAM_STR);
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
    $stmt->bindValue(':categoria', $categoriaHex, PDO::PARAM_STR);

    $stmt->execute();

    $newId = (int)$conn->lastInsertId();

    // 📝 Audit
    Audit::registrarCanvi(
        $conn,
        $userUuid,
        'INSERT',
        "Creació article blog (id=$newId, slug=$slug)",
        Tables::BLOG,
        (string)$newId
    );

    Response::success(
        MissatgesAPI::success('create'),
        [
            'id' => $newId,
            'slug' => $slug,
        ],
        201
    );
} catch (PDOException $e) {
    if ((int)($e->errorInfo[1] ?? 0) === 1062) {
        Response::error(MissatgesAPI::error('duplicat'), ['Registre duplicat'], 409);
    }
    error_log('[blog:post:article] ' . $e->getMessage());
    Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
}
