<?php

declare(strict_types=1);

namespace App\Application\Agenda\UseCase;

use App\Domain\Agenda\Entity\AgendaEvent;
use App\Domain\Agenda\Repository\AgendaRepositoryInterface;
use App\Domain\Agenda\ValueObject\AgendaEstat;
use App\Domain\Agenda\ValueObject\AgendaId;
use App\Domain\Agenda\ValueObject\AgendaTipus;
use App\Domain\Ciutat\ValueObject\CiutatId;

final class CreateAgendaEventUseCase
{
    public function __construct(
        private AgendaRepositoryInterface $repository
    ) {}

    public function execute(array $data): string
    {
        $now = new \DateTimeImmutable();

        $event = new AgendaEvent(

            id: AgendaId::generate(),

            titol: $data['titol'],

            descripcio: $data['descripcio'] ?? null,

            tipus: new AgendaTipus(
                $data['tipus']
            ),

            lloc: $data['lloc'] ?? null,

            ciutatId: !empty($data['ciutat_id'])
                ? CiutatId::fromString(
                    $data['ciutat_id']
                )
                : null,

            dataInici: new \DateTimeImmutable(
                $data['data_inici']
            ),

            dataFi: !empty($data['data_fi'])
                ? new \DateTimeImmutable(
                    $data['data_fi']
                )
                : null,

            totElDia: (bool)$data['tot_el_dia'],

            estat: new AgendaEstat(
                $data['estat']
            ),

            creatEl: $now,

            actualitzatEl: $now
        );

        $this->repository->save($event);

        return $event->getId()->toString();
    }
}
