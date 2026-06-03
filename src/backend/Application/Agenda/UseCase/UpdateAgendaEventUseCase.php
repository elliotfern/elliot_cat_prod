<?php

declare(strict_types=1);

namespace App\Application\Agenda\UseCase;

use App\Domain\Agenda\Repository\AgendaRepositoryInterface;
use App\Domain\Agenda\ValueObject\AgendaId;
use App\Application\Agenda\Factory\AgendaEventFactory;

final class UpdateAgendaEventUseCase
{
    public function __construct(
        private AgendaRepositoryInterface $repository,
        private AgendaEventFactory $factory
    ) {}

    public function execute(AgendaId $id, array $data): string
    {
        $event = $this->repository->findById($id);

        if (!$event) {
            throw new \RuntimeException('Agenda event not found');
        }

        $updated = $this->factory->reconstitute($event, $data);

        $this->repository->save($updated);

        return $updated->getId()->toString();
    }
}
