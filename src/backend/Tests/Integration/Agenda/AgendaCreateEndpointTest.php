<?php

declare(strict_types=1);

namespace App\Tests\Integration\Agenda;

use PHPUnit\Framework\TestCase;
use App\Tests\Support\ApiTestClient;
use App\Config\DatabaseConnection;
use App\Infrastructure\Persistence\Agenda\MysqlAgendaRepository;
use App\Domain\Agenda\ValueObject\AgendaId;

final class AgendaCreateEndpointTest extends TestCase
{
    public function test_it_creates_event_via_api(): void
    {
        $client = new ApiTestClient();

        $pdo = DatabaseConnection::getConnection();
        $repository = new MysqlAgendaRepository($pdo);

        $payload = [
            'titol' => 'Evento test API',
            'descripcio' => 'Test descripció',
            'tipus' => 'altre',
            'lloc' => 'Barcelona',
            'ciutat_id' => '019e7223-646d-71d1-abee-2ed1613c1d0e',
            'data_inici' => '2026-06-10 10:00:00',
            'data_fi' => '2026-06-10 11:00:00',
            'tot_el_dia' => false,
            'estat' => 'confirmat',
        ];

        $response = $client->post('/api/agenda/post', $payload);

        $this->assertEquals(200, $response['status'], $response['raw']);

        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertArrayHasKey('id', $response['body']['data']);

        $id = $response['body']['data']['id'];

        $event = $repository->findById(
            AgendaId::fromString($id)
        );

        $this->assertNotNull($event);
        $this->assertEquals('Evento test API', $event->titol());
    }
}
