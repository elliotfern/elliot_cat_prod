<?php

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://elliot.cat");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Verificar si el tipo es 'mastodon'
if (!isset($_GET['type']) || $_GET['type'] !== 'likes-mastodont') {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo no válido']);
    exit();
}

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


$accessToken = $_ENV['MASTODONTTOKEN']; // Reemplaza con tu token de acceso de Mastodon
$instance = 'mastodont.cat/'; // URL base de Mastodon

// Obtener el JSON enviado desde JavaScript
$data = json_decode(file_get_contents("php://input"), true);
$postId = $data['post_id'] ?? null;

if (!$postId) {
    echo json_encode(["error" => "ID de publicación no recibido"]);
    exit;
}

// Endpoint para hacer "like"
$url = "https://$instance/api/v1/statuses/$postId/favourite";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

// Devolver la respuesta en JSON
if ($response) {
    echo json_encode(["message" => "¡Like enviado con éxito!"]);
} else {
    echo json_encode(["error" => "No se pudo dar like"]);
}
