<?php

declare(strict_types=1);

namespace App\Application\Agenda\UseCase;

use App\Application\Agenda\Factory\AgendaEventFactory;
use App\Domain\Agenda\Repository\AgendaRepositoryInterface;

final class CreateAgendaEventUseCase
{
    public function __construct(
        private AgendaRepositoryInterface $repository,
        private AgendaEventFactory $factory
    ) {}

    public function execute(array $data): string
    {
        $event = $this->factory->create($data);

        $this->repository->save($event);

        return $event->getId()->toString();
    }
}
