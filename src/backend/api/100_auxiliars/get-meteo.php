<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    http_response_code(204);
    exit;
}


// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Meteo Trentino
// ruta GET => "/api/meteo/get"
$api_url = 'https://www.meteotrentino.it/wp-content/uploads/jsonfiles/mappahome.json';

$response = file_get_contents($api_url);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener datos de la API externa']);
    exit;
}

$data = json_decode($response, true);

if (!isset($data['features']) || !is_array($data['features'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Formato de datos incorrecto']);
    exit;
}

// Buscar la estación de Trento que nos interesa
$estacion = null;

foreach ($data['features'] as $feature) {
    if (
        ($feature['properties']['idente'] ?? null) === 'PATUP' &&
        ($feature['properties']['code'] ?? null) === 'T0454'
    ) {
        $estacion = $feature;
        break;
    }
}

if ($estacion === null) {
    http_response_code(404);
    echo json_encode(['error' => 'Estación meteorológica no encontrada']);
    exit;
}

$temperatureData = $estacion['properties']['temperature']['data'] ?? [];

if (empty($temperatureData)) {
    http_response_code(404);
    echo json_encode(['error' => 'No hay datos de temperatura disponibles']);
    exit;
}

// Último dato disponible
$ultimoDato = end($temperatureData);

Response::success(
    message: MissatgesAPI::success('get'),
    data: [
        'estacion' => $estacion['properties']['staz'] ?? 'Trento',
        'temperatura' => $ultimoDato['avg'] ?? null,
        'datetime' => $ultimoDato['datetime'] ?? null,
        'unidad' => $estacion['properties']['temperature']['um'] ?? '°C',
    ],
    httpCode: 200
);
