<?php

declare(strict_types=1);

namespace App\Tests\Integration\Agenda;

use PHPUnit\Framework\TestCase;
use App\Tests\Support\ApiTestClient;
use App\Config\DatabaseConnection;
use App\Infrastructure\Persistence\Agenda\MysqlAgendaRepository;
use App\Domain\Agenda\ValueObject\AgendaId;
use App\Tests\Domain\Agenda\Builder\AgendaEventBuilder;
use PHPUnit\Framework\Attributes\Group;

#[Group('http')]

final class AgendaUpdateEndpointTest extends TestCase
{
    public function test_it_updates_event_via_api(): void
    {
        $client = new ApiTestClient();

        $pdo = DatabaseConnection::getConnection();
        $repository = new MysqlAgendaRepository($pdo);

        // 1. Crear evento inicial en BD
        $event = AgendaEventBuilder::new()
            ->withDateRange(
                '2026-06-10 10:00:00',
                '2026-06-10 11:00:00'
            )
            ->build();

        $repository->save($event);

        $id = $event->getId()->toString();

        // 2. Payload update
        $payload = [
            'id' => $id,
            'titol' => 'Evento actualizado',
            'descripcio' => 'Updated desc',
            'tipus' => 'viatge',
            'lloc' => 'Madrid',
            'ciutat_id' => '019e7223-646d-71d1-abee-2ed1613c1d0e',
            'data_inici' => '2026-06-10 12:00:00',
            'data_fi' => '2026-06-10 13:00:00',
            'tot_el_dia' => false,
            'estat' => 'confirmat',
        ];

        // 3. Llamada API
        $response = $client->put("/api/agenda/put/$id", $payload);

        $this->assertEquals(200, $response['status'], $response['raw']);

        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('data', $response['body']);

        // 4. Verificar en BD
        $updated = $repository->findById(
            AgendaId::fromString($id)
        );

        $this->assertNotNull($updated);
        $this->assertEquals('Evento actualizado', $updated->titol());
        $this->assertEquals('Madrid', $updated->lloc());
    }
}
