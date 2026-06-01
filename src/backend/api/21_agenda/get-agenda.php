<?php

use App\Application\Agenda\UseCase\GetAgendaByIdUseCase;
use App\Application\Agenda\UseCase\GetAgendaFutureEventsUseCase;
use App\Application\Agenda\UseCase\GetAgendaRangeUseCase;
use App\Application\Agenda\Service\BirthdayService;
use App\Config\Database;
use App\Config\DatabaseConnection;
use App\Infrastructure\Persistence\Agenda\MysqlAgendaRepository;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Domain\Agenda\ValueObject\AgendaId;
use App\Infrastructure\Persistence\Ciutat\MysqlCiutatRepository;

$slug = $routeParams[0] ?? null;

$db  = new Database();
$pdo = DatabaseConnection::getConnection();

$agendaRepository = new MysqlAgendaRepository($pdo);
$birthdayService = new BirthdayService($pdo);
$ciutatRepository = new MysqlCiutatRepository($pdo);

/*
 * BACKEND AGENDA
 * GET ESDEVENIMENTS
 */

// Configuración de cabeceras para aceptar JSON y responder JSON
// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

// Verificar que el método de la solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

/**
 * GET : Esdeveniment per ID
 * URL: https://elliot.cat/api/agenda/get/esdevenimentId?id=1
 */
if ($slug === "esdevenimentId") {

    $id = $_GET['id'];
    $agendaId = AgendaId::fromString($id);

    if (!$id) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['Paràmetre id requerit'],
            400
        );
        return;
    }

    try {

        $useCase = new GetAgendaByIdUseCase(
            $agendaRepository,
            $ciutatRepository
        );

        $data = $useCase->execute($agendaId);

        if (!$data) {
            Response::error(
                MissatgesAPI::error('notFound'),
                ['Esdeveniment no trobat'],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $data,
            httpCode: 200
        );
    } catch (Throwable $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    /**
     * GET : Llistat d’esdeveniments futurs - 
     * URL: /agenda/get/esdevenimentsFuturs?usuari_id=1
     */
} else if ($slug === "esdevenimentsFuturs") {
    $usuariId = 1;

    if (!$usuariId) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['Paràmetre requerit: usuari_id'],
            400
        );
        return;
    }

    $useCase = new GetAgendaFutureEventsUseCase(
        $agendaRepository,
    );

    try {

        $data = $useCase->execute();

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $data,
            httpCode: 200
        );
    } catch (Throwable $e) {

        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }


    /**
     * GET : Llistat d’esdeveniments per rang de dates
     * URL: agenda/get/esdevenimentsRang?usuari_id=1&from=2025-01-01&to=2025-01-31
     */
} else if ($slug === "esdevenimentsRang") {

    $usuariId = (int)($_GET['usuari_id'] ?? 0);
    $from     = $_GET['from'] ?? null;
    $to       = $_GET['to'] ?? null;

    error_log(json_encode([$from, $to]));

    if ($usuariId <= 0 || !$from || !$to) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['Paràmetres requerits: usuari_id, from, to'],
            400
        );
        return;
    }

    if (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)
    ) {
        Response::error(
            MissatgesAPI::error('validacio'),
            ['Format de data invàlid. Usa YYYY-MM-DD'],
            400
        );
        return;
    }

    try {

        $useCase = new GetAgendaRangeUseCase(
            $agendaRepository,
            $birthdayService
        );

        $result = $useCase->execute($from, $to);

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (\Throwable $e) {

        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }
} else {
    // Slug no reconocido
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Something get wrong']);
    exit();
}
