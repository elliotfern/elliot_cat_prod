<?php

declare(strict_types=1);

namespace App\Application\Agenda\UseCase;

use App\Domain\Agenda\Repository\AgendaRepositoryInterface;
use App\Domain\Agenda\ValueObject\AgendaId;
use App\Domain\Agenda\ValueObject\AgendaTipus;
use App\Domain\Agenda\ValueObject\AgendaEstat;
use App\Domain\Agenda\Entity\AgendaEvent;
use App\Domain\Ciutat\ValueObject\CiutatId;
use App\Domain\Shared\ValueObject\DateRange;

final class UpdateAgendaEventUseCase
{
    public function __construct(
        private AgendaRepositoryInterface $repository
    ) {}

    public function execute(AgendaId $id, array $data): string
    {
        $event = $this->repository->findById($id);

        if (!$event) {
            throw new \RuntimeException('Agenda event not found');
        }

        $dateRange = new DateRange(
            new \DateTimeImmutable($data['data_inici']),
            !empty($data['data_fi'])
                ? new \DateTimeImmutable($data['data_fi'])
                : null
        );

        $updated = new AgendaEvent(
            id: $event->getId(),
            titol: $data['titol'],
            descripcio: $data['descripcio'] ?? null,
            tipus: new AgendaTipus($data['tipus']),
            lloc: $data['lloc'] ?? null,
            ciutatId: !empty($data['ciutat_id'])
                ? CiutatId::fromString($data['ciutat_id'])
                : null,
            dateRange: $dateRange,
            totElDia: (bool)$data['tot_el_dia'],
            estat: new AgendaEstat($data['estat']),
            creatEl: $event->creatEl(),
            actualitzatEl: new \DateTimeImmutable()
        );

        $this->repository->save($updated);

        return $updated->getId()->toString();
    }
}
