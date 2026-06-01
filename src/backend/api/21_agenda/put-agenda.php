<?php

declare(strict_types=1);

use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Application\Agenda\Schema\AgendaSchema;
use App\Application\Agenda\UseCase\UpdateAgendaEventUseCase;
use App\Domain\Agenda\ValueObject\AgendaId;
use App\Config\DatabaseConnection;
use App\Infrastructure\Persistence\Agenda\MysqlAgendaRepository;
use App\Utils\Schema\SchemaProcessor;
use App\Utils\Schema\SchemaValidationException;

$pdo = DatabaseConnection::getConnection();

/** @var array $routeParams */
$id = $routeParams[0] ?? null;

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    Response::error(MissatgesAPI::error('method_not_allowed'), [], 405);
    exit;
}

try {

    // -------------------------
    // ID (param)
    // -------------------------

    $id_param = $id ?? null;

    if (!$id_param) {
        Response::error('ID obligatori', [], 400);
        return;
    }

    $agendaId = AgendaId::fromString($id_param);

    // -------------------------
    // INPUT JSON
    // -------------------------

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!$data || !is_array($data)) {

        Response::error(
            "Payload invàlid.",
            [],
            400
        );

        return;
    }

    // -------------------------
    // SCHEMA VALIDATION
    // -------------------------

    try {

        $schema = AgendaSchema::update();

        $agendaData = SchemaProcessor::process(
            $data,
            $schema
        );
    } catch (SchemaValidationException $e) {

        Response::error(
            MissatgesAPI::error('validacio'),
            $e->toApiArray(),
            400
        );

        return;
    }

    // -------------------------
    // USE CASE
    // -------------------------

    $repository = new MysqlAgendaRepository($pdo);

    $useCase = new UpdateAgendaEventUseCase($repository);

    $useCase->execute(
        $agendaId,
        $agendaData
    );

    // -------------------------
    // RESPONSE
    // -------------------------

    Response::success(
        message: MissatgesAPI::success('update'),
        data: ['id' => $id],
        httpCode: 200
    );
} catch (\Throwable $e) {

    Response::error(
        "S'ha produït un error a la base de dades.",
        [
            $e->getMessage()
        ]
    );
}
