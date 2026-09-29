<?php

use App\Application\Agenda\UseCase\DeleteAgendaEventUseCase;
use App\Config\DatabaseConnection;
use App\Infrastructure\EntryPoint\Http\Agenda\DeleteAgendaEventController;
use App\Infrastructure\Persistence\Agenda\MysqlAgendaRepository;
use App\Infrastructure\Security\Auth\AuthFactory;
use App\Utils\MissatgesAPI;
use App\Utils\Response;

$slug = $routeParams[0] ?? null;

$pdo = DatabaseConnection::getConnection();
$agendaRepository = new MysqlAgendaRepository($pdo);

// Configuración de cabeceras para aceptar JSON y responder JSON
// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

// Verificar que el método de la solicitud sea DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

/**
 * DELETE : Esdeveniment per ID 
 * URL: https://elliot.cat/api/agenda/delete/esdevenimentId?id=1
 */
if ($slug === "esdeveniment") {

    AuthFactory::admin()->handle();

    $id = $_GET['id'] ?? '';

    if ($id === '') {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['Paràmetre id requerit'],
            400
        );
        return;
    }

    $controller = new DeleteAgendaEventController(
        new DeleteAgendaEventUseCase($agendaRepository)
    );

    $controller->execute($id);
} else {
    // Slug no reconocido
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
