<?php

declare(strict_types=1);

namespace App\Tests\Integration\Agenda;

use PHPUnit\Framework\TestCase;
use App\Config\DatabaseConnection;
use App\Infrastructure\Persistence\Agenda\MysqlAgendaRepository;
use App\Tests\Domain\Agenda\Builder\AgendaEventBuilder;

final class AgendaRangeRepositoryTest extends TestCase
{
    public function test_it_finds_events_by_date_range(): void
    {
        $pdo = DatabaseConnection::getConnection();

        $repository = new MysqlAgendaRepository($pdo);

        // Evento dentro del rango
        $event = AgendaEventBuilder::new()
            ->withDateRange(
                '2026-06-15 10:00:00',
                '2026-06-15 11:00:00'
            )
            ->build();

        $repository->save($event);

        $events = $repository->findByDateRange(
            new \DateTimeImmutable('2026-06-01'),
            new \DateTimeImmutable('2026-06-30')
        );

        $found = false;

        foreach ($events as $e) {

            if (
                $e->getId()->toString()
                === $event->getId()->toString()
            ) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }

    public function test_it_does_not_return_events_outside_range(): void
    {
        $pdo = DatabaseConnection::getConnection();

        $repository = new MysqlAgendaRepository($pdo);

        // Evento FUERA de junio
        $event = AgendaEventBuilder::new()
            ->withDateRange(
                '2026-08-15 10:00:00',
                '2026-08-15 11:00:00'
            )
            ->build();

        $repository->save($event);

        $events = $repository->findByDateRange(
            new \DateTimeImmutable('2026-06-01'),
            new \DateTimeImmutable('2026-06-30')
        );

        $found = false;

        foreach ($events as $e) {

            if (
                $e->getId()->toString()
                === $event->getId()->toString()
            ) {
                $found = true;
                break;
            }
        }

        $this->assertFalse($found);
    }

    public function test_it_returns_events_that_overlap_range(): void
    {
        $pdo = DatabaseConnection::getConnection();

        $repository = new MysqlAgendaRepository($pdo);

        // Evento empieza antes pero termina dentro
        $event = AgendaEventBuilder::new()
            ->withDateRange(
                '2026-05-30 10:00:00',
                '2026-06-03 12:00:00'
            )
            ->build();

        $repository->save($event);

        $events = $repository->findByDateRange(
            new \DateTimeImmutable('2026-06-01'),
            new \DateTimeImmutable('2026-06-30')
        );

        $found = false;

        foreach ($events as $e) {

            if (
                $e->getId()->toString()
                === $event->getId()->toString()
            ) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }
}
