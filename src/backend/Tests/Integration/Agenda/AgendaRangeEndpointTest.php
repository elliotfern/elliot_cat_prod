<?php

declare(strict_types=1);

namespace App\Tests\Integration\Agenda;

use PHPUnit\Framework\TestCase;
use App\Config\DatabaseConnection;
use App\Infrastructure\Persistence\Agenda\MysqlAgendaRepository;
use App\Tests\Domain\Agenda\Builder\AgendaEventBuilder;
use App\Tests\Support\ApiTestClient;
use PHPUnit\Framework\Attributes\Group;

#[Group('http')]

final class AgendaRangeEndpointTest extends TestCase
{
    public function test_it_returns_events_from_api_endpoint(): void
    {
        $pdo = DatabaseConnection::getConnection();
        $repository = new MysqlAgendaRepository($pdo);

        // Crear evento en BD
        $event = AgendaEventBuilder::new()
            ->withDateRange(
                '2026-06-15 10:00:00',
                '2026-06-15 11:00:00'
            )
            ->build();

        $repository->save($event);

        // Cliente API (usa TEST_BASE_URL si existe)
        $client = new ApiTestClient();

        $response = $client->get(
            '/api/agenda/get/esdevenimentsRang',
            [
                'usuari_id' => 1,
                'from' => '2026-06-01',
                'to' => '2026-06-30'
            ]
        );

        // 🔥 DEBUG útil si falla en CI
        $this->assertNotEmpty(
            $response['raw'] ?? null,
            'La API no ha devuelto respuesta'
        );

        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('data', $response['body']);

        $found = false;

        foreach ($response['body']['data'] as $item) {
            if ($item['id'] === $event->getId()->toString()) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }
}
