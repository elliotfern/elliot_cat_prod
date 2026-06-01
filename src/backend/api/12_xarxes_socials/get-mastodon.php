<?php

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://elliot.cat");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

// Verificar si el tipo es 'mastodon'
if (!isset($_GET['type']) || $_GET['type'] !== 'feed-mastodon') {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo no válido']);
    exit();
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$accessToken = $_ENV['MASTODONTTOKEN']; // Reemplaza con tu token de acceso de Mastodon
$instance = 'mastodont.cat/'; // URL base de Mastodon


$url = "https://$instance/api/v1/timelines/home";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken"
]);

$response = curl_exec($ch);
curl_close($ch);

// Decodificar la respuesta JSON en un array PHP
$responseData = json_decode($response, true);

if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 401) {
    http_response_code(401);
    echo json_encode(['error' => 'Token de acceso inválido o caducado']);
    exit();
}

// Verificar si la respuesta es válida
if ($responseData === null) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al decodificar la respuesta de la API']);
    exit();
}

// Devolver la respuesta como JSON
echo json_encode($responseData);
