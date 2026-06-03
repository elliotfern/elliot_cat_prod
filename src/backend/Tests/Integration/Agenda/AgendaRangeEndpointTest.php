<?php

declare(strict_types=1);

namespace App\Tests\Integration\Agenda;

use PHPUnit\Framework\TestCase;
use App\Config\DatabaseConnection;
use App\Infrastructure\Persistence\Agenda\MysqlAgendaRepository;
use App\Tests\Domain\Agenda\Builder\AgendaEventBuilder;

final class AgendaRangeEndpointTest extends TestCase
{
    public function test_it_returns_events_from_api_endpoint(): void
    {
        $pdo = DatabaseConnection::getConnection();
        $repository = new MysqlAgendaRepository($pdo);

        // Creamos evento en BD
        $event = AgendaEventBuilder::new()
            ->withDateRange(
                '2026-06-15 10:00:00',
                '2026-06-15 11:00:00'
            )
            ->build();

        $repository->save($event);

        $url = 'http://localhost/api/agenda/get/esdevenimentsRang'
            . '?usuari_id=1'
            . '&from=2026-06-01'
            . '&to=2026-06-30';

        $responseRaw = file_get_contents($url);

        $this->assertNotFalse($responseRaw);

        $response = json_decode($responseRaw, true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);

        $found = false;

        foreach ($response['data'] as $item) {
            if ($item['id'] === $event->getId()->toString()) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }
}
