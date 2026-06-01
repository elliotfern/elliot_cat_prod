<?php
header('Content-Type: application/json');

// URL de la API original (reemplaza con tu queryKey completa)
$api_url = 'https://api.3cat.cat/arafem?_format=json&cadena=cm&r=yes&tipus=radio&version=1.0';

$response = file_get_contents($api_url);

if ($response === FALSE) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener datos de la API externa']);
    exit;
}

echo $response;
