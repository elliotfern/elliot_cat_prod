<?php

use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\AdminMiddleware;
use App\Services\ClientService;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

$db = new Database();
$pdo = $db->getPdo();

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: DELETE");

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);


// Verificar que el método de la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}


if ($slug === 'client') {

    AdminMiddleware::handle();

    $id = $_GET['id'] ?? null;

    if (!$id) {
        Response::error(
            message: MissatgesAPI::error('validacio'),
            errors: ['ID requerit'],
            httpCode: 400
        );
        return;
    }

    $clientService = new ClientService($db);

    try {

        $result = $clientService->delete($id);

        Response::success(
            message: MissatgesAPI::success('delete'),
            data: $result,
            httpCode: 200
        );
    } catch (Throwable $e) {

        Response::error(
            message: MissatgesAPI::error('errorBD'),
            errors: [$e->getMessage()],
            httpCode: 500
        );
    }
}
