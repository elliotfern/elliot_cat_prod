<?php

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://elliot.cat");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Permitir solo origen autorizado
$allowed_origins = ['https://elliot.cat'];
if (!isset($_SERVER['HTTP_ORIGIN']) || !in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no permitido']);
    exit();
}

// Verificar si el tipo es 'blueSky'
if (!isset($_GET['type']) || $_GET['type'] !== 'blog') {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo no válido']);
    exit();
}

function generarUUID()
{
    $uuid = sprintf('%04d-%04d', mt_rand(1000, 9999), mt_rand(1000, 9999));
    return $uuid;
}

function generateDate()
{
    $date = date("Ymd-His");
    return $date;
}

function generateDateEsp()
{
    $date = date("d/m/Y");
    return $date;
}

// Cambié el formato a día-mes-año

if (empty($_POST['mensaje'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El mensaje no puede estar vacío']);
    exit();
}

// Recoger datos del formulario
$post_content = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';
$post_title = "Microblogging " . generateDateEsp();
$post_type = "blog";
$post_excerpt = NULL;
$lang = 1;
$post_status = "publish";
$slug = "microblogging-" . generateDate();
$categoria = 137;
$post_date = date("Y-m-d H:i:s");

// Conectar a la base de datos con PDO (asegúrate de modificar los detalles de la conexión)
try {

    global $conn;
    /** @var PDO $conn */

    // Crear la consulta SQL
    $sql = "INSERT INTO db_blog (
    post_type, post_title, post_content, post_excerpt, lang, post_status, slug, categoria, post_date
    ) VALUES (
    :post_type, :post_title, :post_content, :post_excerpt, :lang, :post_status, :slug, :categoria, :post_date
    )";

    // Preparar la consulta
    $stmt = $conn->prepare($sql);

    // Enlazar los parámetros con los valores de las variables PHP
    $stmt->bindParam(':post_type', $post_type, PDO::PARAM_STR);
    $stmt->bindParam(':post_title', $post_title, PDO::PARAM_STR);
    $stmt->bindParam(':post_content', $post_content, PDO::PARAM_STR);
    $stmt->bindParam(':post_excerpt', $post_excerpt, PDO::PARAM_STR);
    $stmt->bindParam(':lang', $lang, PDO::PARAM_INT);
    $stmt->bindParam(':post_status', $post_status, PDO::PARAM_STR);
    $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
    $stmt->bindParam(':categoria', $categoria, PDO::PARAM_INT);
    $stmt->bindParam(':post_date', $post_date, PDO::PARAM_STR);

    // Ejecutar la consulta
    $stmt->execute();

    // Respuesta de éxito
    echo json_encode(["status" => "success", "message" => "✅ Les dades s'han desat correctament a la base de dades."]);
} catch (PDOException $e) {
    // En caso de error en la conexión o ejecución de la consulta
    http_response_code(500); // Internal Server Error
    echo json_encode(["status" => "error", "message" => "S'ha produit un error a la base de dades: " . $e->getMessage()]);
}
