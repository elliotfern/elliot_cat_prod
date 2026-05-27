<?php

declare(strict_types=1);

namespace App\Domain\Agenda\Repository;

use App\Domain\Agenda\Entity\AgendaEvent;
use App\Domain\Agenda\ValueObject\AgendaId;
use DateTimeImmutable;

interface AgendaRepositoryInterface
{
    public function findById(
        AgendaId $id
    ): ?AgendaEvent;

    /**
     * @return AgendaEvent[]
     */
    public function findByDateRange(
        DateTimeImmutable $from,
        DateTimeImmutable $to
    ): array;

    /**
     * @return AgendaEvent[]
     */
    public function findFutureEvents(): array;

    public function save(
        AgendaEvent $event
    ): void;

    public function delete(
        AgendaId $id
    ): void;
}
