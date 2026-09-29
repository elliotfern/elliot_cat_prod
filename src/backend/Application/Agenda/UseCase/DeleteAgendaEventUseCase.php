<?php

declare(strict_types=1);

namespace App\Application\Agenda\UseCase;

use App\Domain\Agenda\Repository\AgendaRepositoryInterface;
use App\Domain\Agenda\ValueObject\AgendaId;
use App\Domain\Shared\Exception\NotFoundException;

final class DeleteAgendaEventUseCase
{
    public function __construct(
        private AgendaRepositoryInterface $agendaRepository
    ) {}

    public function execute(string $id): void
    {
        $agendaId = AgendaId::fromString($id);

        if ($this->agendaRepository->findById($agendaId) === null) {
            throw new NotFoundException('Esdeveniment no trobat');
        }

        $this->agendaRepository->delete($agendaId);
    }
}
