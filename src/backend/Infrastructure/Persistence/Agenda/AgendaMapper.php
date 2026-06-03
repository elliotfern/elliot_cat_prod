<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Agenda;

use App\Domain\Agenda\Entity\AgendaEvent;
use App\Domain\Agenda\ValueObject\AgendaEstat;
use App\Domain\Agenda\ValueObject\AgendaId;
use App\Domain\Agenda\ValueObject\AgendaTipus;
use App\Domain\Ciutat\ValueObject\CiutatId;
use App\Domain\Shared\ValueObject\DateRange;
use DateTimeImmutable;

final class AgendaMapper
{
    /**
     * @param array<string,mixed> $row
     */

    public static function toDomain(array $row): AgendaEvent
    {
        $dateRange = new DateRange(
            new DateTimeImmutable((string)$row['data_inici']),
            !empty($row['data_fi'])
                ? new DateTimeImmutable((string)$row['data_fi'])
                : null
        );

        return new AgendaEvent(
            id: new AgendaId($row['id']),
            titol: (string)$row['titol'],
            descripcio: isset($row['descripcio'])
                ? (string)$row['descripcio']
                : null,
            tipus: new AgendaTipus((string)$row['tipus']),
            lloc: isset($row['lloc'])
                ? (string)$row['lloc']
                : null,
            ciutatId: new CiutatId($row['ciutat_id']),
            dateRange: $dateRange,

            totElDia: (bool)$row['tot_el_dia'],
            estat: new AgendaEstat((string)$row['estat']),
            creatEl: new DateTimeImmutable((string)$row['creat_el']),
            actualitzatEl: new DateTimeImmutable((string)$row['actualitzat_el'])
        );
    }
}
