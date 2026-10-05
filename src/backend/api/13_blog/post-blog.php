<?php

declare(strict_types=1);

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;
use App\Utils\ValidacioErrors;
use App\Config\DatabaseConnection;
use App\Infrastructure\Error\HttpErrorResponder;
use App\Utils\Uuid;
use Ramsey\Uuid\Uuid as RamseyUuid;

header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$conn = DatabaseConnection::getConnection();
if (!$conn) {
    Response::error(MissatgesAPI::error('errorBD'), ['No se pudo establecer conexión a la base de datos.'], 500);
}


// 📨 Entrada JSON
$inputData = file_get_contents('php://input');
$data = json_decode($inputData, true) ?: [];

$errors = [];

/**
 * =========================
 * CREATE ID (UUID v7)
 * =========================
 */
$id = RamseyUuid::uuid7()->toString();
$idBin = Uuid::toBinary($id);


// 📥 Campos (post_date y post_modified se ignoran: backend los gestiona)
$post_type    = isset($data['post_type']) ? trim((string)$data['post_type']) : 'post';
$post_title   = isset($data['post_title']) ? trim((string)$data['post_title']) : '';
$post_content = isset($data['post_content']) ? (string)$data['post_content'] : '';
$post_excerpt = array_key_exists('post_excerpt', $data) ? trim((string)($data['post_excerpt'] ?? '')) : null;

$idioma_id         = $data['idioma_id'];
$post_status  = isset($data['post_status']) ? trim((string)$data['post_status']) : 'publish';
$slug         = isset($data['slug']) ? trim((string)$data['slug']) : '';

$categoria_id = isset($data['categoria_id']) ? trim((string)$data['categoria_id']) : '';

// 🔎 Validaciones
if ($post_title === '') $errors[] = ValidacioErrors::requerit('post_title');
if ($post_content === '') $errors[] = ValidacioErrors::requerit('post_content');

if ($idioma_id === null) {
    $errors[] = ValidacioErrors::requerit('idioma_id');
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

if ($categoria_id === '') {
    $errors[] = ValidacioErrors::requerit('categoria');
} elseif (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $categoria_id)) {
    $errors[] = ValidacioErrors::format('categoria',);
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
      id, post_type, post_title, post_content, post_excerpt, idioma_id, post_status, slug, categoria_id, post_date, post_modified
    ) VALUES (
      :id, :post_type, :post_title, :post_content, :post_excerpt, :idioma_id, :post_status, :slug,  :categoria_id, NOW(), NOW()
    )
  ";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':id', $idBin, PDO::PARAM_LOB);
    $stmt->bindValue(':post_type', $post_type, PDO::PARAM_STR);
    $stmt->bindValue(':post_title', $post_title, PDO::PARAM_STR);
    $stmt->bindValue(':post_content', $post_content, PDO::PARAM_STR);

    // excerpt nullable
    $excerptVal = ($post_excerpt !== null && $post_excerpt !== '') ? $post_excerpt : null;
    $stmt->bindValue(':post_excerpt', $excerptVal, $excerptVal === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

    $stmt->bindValue(':idioma_id', uuid::toBinary($idioma_id), PDO::PARAM_LOB);
    $stmt->bindValue(':post_status', $post_status, PDO::PARAM_STR);
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
    $stmt->bindValue(':categoria_id', Uuid::toBinary($categoria_id), PDO::PARAM_LOB);

    $stmt->execute();

    Response::success(
        MissatgesAPI::success('create'),
        [
            'id' => $id,
            'slug' => $slug,
        ],
        httpCode: 201
    );
} catch (PDOException $e) {

    Response::error(MissatgesAPI::error('errorBD'), [$e->getMessage()], 500);
}
