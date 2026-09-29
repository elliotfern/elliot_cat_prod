<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Agenda;

use App\Application\Agenda\UseCase\DeleteAgendaEventUseCase;
use App\Domain\Shared\Exception\NotFoundException;
use App\Domain\Shared\Exception\ValidationException;
use App\Utils\MissatgesAPI;
use App\Utils\Response;

final class DeleteAgendaEventController
{
    public function __construct(
        private DeleteAgendaEventUseCase $deleteUseCase
    ) {}

    public function execute(string $id): void
    {
        try {
            $this->deleteUseCase->execute($id);

            Response::success(
                message: MissatgesAPI::success('delete'),
                data: [],
                httpCode: 200
            );
        } catch (ValidationException $e) {
            Response::error(
                MissatgesAPI::error('validacio'),
                [$e->getMessage()],
                400
            );
        } catch (NotFoundException $e) {
            Response::error(
                MissatgesAPI::error('notFound'),
                [$e->getMessage()],
                404
            );
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            Response::error(
                MissatgesAPI::error('errorBD'),
                [],
                500
            );
        }
    }
}