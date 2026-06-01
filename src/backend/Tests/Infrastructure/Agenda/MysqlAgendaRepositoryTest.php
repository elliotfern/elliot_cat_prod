<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Agenda;

use PHPUnit\Framework\TestCase;
use App\Config\DatabaseConnection;
use App\Infrastructure\Persistence\Agenda\MysqlAgendaRepository;
use App\Domain\Agenda\Entity\AgendaEvent;
use App\Tests\Domain\Agenda\Builder\AgendaEventBuilder;


final class MysqlAgendaRepositoryTest extends TestCase
{
    private \PDO $pdo;
    private MysqlAgendaRepository $repository;

    protected function setUp(): void
    {

        $this->markTestSkipped('Temporalmente desactivado hasta configurar DB local');
        DatabaseConnection::reset();

        $this->pdo = DatabaseConnection::getConnection();
        $this->repository = new MysqlAgendaRepository($this->pdo);

        // limpiar tabla
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        $this->pdo->exec("TRUNCATE TABLE db_agenda_events");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    }

    public function test_it_saves_and_retrieves_event(): void
    {
        $this->markTestSkipped('Temporalmente desactivado hasta configurar DB local');

        // ARRANGE
        $event = AgendaEventBuilder::new()
            ->build();

        // ACT
        $this->repository->save($event);

        $result = $this->repository->findById($event->getId());

        // ASSERT
        $this->assertNotNull($result);
        $this->assertEquals(
            $event->titol(),
            $result->titol()
        );

        $this->assertEquals(
            $event->tipus()->value(),
            $result->tipus()->value()
        );
    }
}
