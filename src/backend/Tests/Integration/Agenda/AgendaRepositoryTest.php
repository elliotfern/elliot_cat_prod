<?php

declare(strict_types=1);

namespace App\Tests\Integration\Agenda;

use PHPUnit\Framework\TestCase;
use App\Config\DatabaseConnection;
use App\Infrastructure\Persistence\Agenda\MysqlAgendaRepository;
use App\Tests\Domain\Agenda\Builder\AgendaEventBuilder;

final class AgendaRepositoryTest extends TestCase
{
    private \PDO $pdo;
    private MysqlAgendaRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = DatabaseConnection::getConnection();
        $this->repository = new MysqlAgendaRepository($this->pdo);

        // 🔥 aislamiento del test
        $this->pdo->beginTransaction();
    }

    protected function tearDown(): void
    {
        // 🔥 rollback automático
        $this->pdo->rollBack();
    }

    public function test_it_saves_and_reads_event_from_database(): void
    {
        // ARRANGE
        $event = AgendaEventBuilder::new()->build();

        // ACT
        $this->repository->save($event);

        $loaded = $this->repository->findById($event->getId());

        // ASSERT
        $this->assertNotNull($loaded);
        $this->assertEquals($event->getId()->toString(), $loaded->getId()->toString());
        $this->assertEquals($event->titol(), $loaded->titol());
        $this->assertEquals(
            $event->dataInici()->format('Y-m-d H:i:s'),
            $loaded->dataInici()->format('Y-m-d H:i:s')
        );
    }
}
