<?php

declare(strict_types=1);

namespace App\Application\Agenda\Factory;

use App\Domain\Agenda\Entity\AgendaEvent;
use App\Domain\Agenda\ValueObject\AgendaEstat;
use App\Domain\Agenda\ValueObject\AgendaId;
use App\Domain\Agenda\ValueObject\AgendaTipus;
use App\Domain\Ciutat\ValueObject\CiutatId;
use App\Domain\Shared\ValueObject\DateRange;

final class AgendaEventFactory
{
    public function create(array $data): AgendaEvent
    {
        return new AgendaEvent(
            id: AgendaId::generate(),
            titol: $data['titol'],
            descripcio: $data['descripcio'] ?? null,
            tipus: new AgendaTipus($data['tipus']),
            lloc: $data['lloc'] ?? null,
            ciutatId: !empty($data['ciutat_id'])
                ? CiutatId::fromString($data['ciutat_id'])
                : null,
            dateRange: DateRange::fromStrings(
                $data['data_inici'],
                $data['data_fi'] ?? null
            ),
            totElDia: (bool)($data['tot_el_dia'] ?? false),
            estat: new AgendaEstat($data['estat']),
            creatEl: new \DateTimeImmutable(),
            actualitzatEl: new \DateTimeImmutable()
        );
    }

    public function reconstitute(AgendaEvent $event, array $data): AgendaEvent
    {
        return new AgendaEvent(
            id: $event->getId(),
            titol: $data['titol'],
            descripcio: $data['descripcio'] ?? null,
            tipus: new AgendaTipus($data['tipus']),
            lloc: $data['lloc'] ?? null,
            ciutatId: !empty($data['ciutat_id'])
                ? CiutatId::fromString($data['ciutat_id'])
                : null,
            dateRange: DateRange::fromStrings(
                $data['data_inici'],
                $data['data_fi'] ?? null
            ),
            totElDia: (bool)($data['tot_el_dia'] ?? false),
            estat: new AgendaEstat($data['estat']),
            creatEl: $event->creatEl(),
            actualitzatEl: new \DateTimeImmutable()
        );
    }
}
