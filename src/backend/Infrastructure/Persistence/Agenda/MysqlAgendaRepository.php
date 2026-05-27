<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Agenda;

use App\Domain\Agenda\Entity\AgendaEvent;
use App\Domain\Agenda\Repository\AgendaRepositoryInterface;
use App\Domain\Agenda\ValueObject\AgendaId;
use DateTimeImmutable;
use PDO;

final class MysqlAgendaRepository implements AgendaRepositoryInterface
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function findById(AgendaId $id): ?AgendaEvent
    {
        $sql = "SELECT 
        e.id,
        e.titol,
        e.descripcio,
        e.tipus,
        e.lloc,
        e.ciutat_id,
        e.data_inici,
        e.data_fi,
        e.tot_el_dia,
        e.estat,
        e.creat_el,
        e.actualitzat_el,
        c.ciutat AS ciutat_nom,
        c.ciutat_ca AS ciutat_ca
    FROM db_agenda_esdeveniments AS e
    LEFT JOIN db_geo_ciutats c ON e.ciutat_id = c.id
    WHERE e.id = :id
    LIMIT 1";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id->value()
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return AgendaMapper::toDomain($row);
    }

    public function findByDateRange(
        DateTimeImmutable $from,
        DateTimeImmutable $to
    ): array {

        $sql = "SELECT 
          e.id,
        e.titol,
        e.descripcio,
        e.tipus,
        e.lloc,
        e.ciutat_id,
        e.data_inici,
        e.data_fi,
        e.tot_el_dia,
        e.estat,
        e.creat_el,
        e.actualitzat_el,
        c.ciutat AS ciutat_nom,
        c.ciutat_ca AS ciutat_ca
    FROM db_agenda_esdeveniments AS e
    LEFT JOIN db_geo_ciutats c ON e.ciutat_id = c.id
    WHERE data_inici >= :from
      AND data_inici <= :to
    ORDER BY data_inici ASC";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':from' => $from->format('Y-m-d H:i:s'),
            ':to'   => $to->format('Y-m-d H:i:s'),
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static fn($row) => AgendaMapper::toDomain($row),
            $rows
        );
    }

    public function findFutureEvents(): array
    {
        $sql = "SELECT 
          e.id,
        e.titol,
        e.descripcio,
        e.tipus,
        e.lloc,
        e.ciutat_id,
        e.data_inici,
        e.data_fi,
        e.tot_el_dia,
        e.estat,
        e.creat_el,
        e.actualitzat_el,
        c.ciutat AS ciutat_nom,
        c.ciutat_ca AS ciutat_ca
    FROM db_agenda_esdeveniments AS e
    LEFT JOIN db_geo_ciutats c ON e.ciutat_id = c.id
    WHERE data_inici >= NOW()
    ORDER BY data_inici ASC";
        $stmt = $this->pdo->query($sql);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static fn($row) => AgendaMapper::toDomain($row),
            $rows
        );
    }

    public function save(AgendaEvent $event): void
    {
        // lo dejamos para el siguiente paso (write side)
    }

    public function delete(AgendaId $id): void
    {
        $sql = "DELETE FROM db_agenda_esdeveniments
            WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id->value()
        ]);
    }
}
