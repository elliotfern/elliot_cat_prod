<?php

declare(strict_types=1);

// Simula entorno web
$_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';

// Bootstrap de la app
require __DIR__ . '/../src/backend/bootstrap.php';

// Router manual (IMPORTANTE: ajusta a tu estructura real)
$routeParams = [];


// reconstruir ruta tipo /api/agenda/...
$uri = $_GET['route'] ?? '';

$routeParams = explode('/', trim($uri, '/'));
$slug = $routeParams[2] ?? null;

if (!$uri) {
    http_response_code(404);
    echo json_encode(['error' => 'No route']);
    exit;
}

// Simular PATH_INFO
$parts = explode('/', trim($uri, '/'));
$routeParams = $parts;

// Cargar router principal de agenda
require __DIR__ . '/../src/backend/api/21_agenda/get-agenda.php';
