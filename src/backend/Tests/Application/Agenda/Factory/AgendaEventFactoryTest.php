<?php

declare(strict_types=1);

namespace App\Tests\Application\Agenda\Factory;

use PHPUnit\Framework\TestCase;
use App\Application\Agenda\Factory\AgendaEventFactory;
use App\Domain\Agenda\Entity\AgendaEvent;
use InvalidArgumentException;

final class AgendaEventFactoryTest extends TestCase
{
    public function test_it_creates_valid_agenda_event(): void
    {
        $factory = new AgendaEventFactory();

        $event = $factory->create([
            'titol' => 'Reunió test',
            'descripcio' => 'Desc',
            'tipus' => 'reunio',
            'estat' => 'confirmat',
            'lloc' => 'Barcelona',
            'ciutat_id' => '019e7223-646d-71d1-abee-2ed1613c1d0e',
            'data_inici' => '2026-06-01 10:00:00',
            'data_fi' => '2026-06-01 11:00:00',
            'tot_el_dia' => false,
        ]);

        $this->assertInstanceOf(
            AgendaEvent::class,
            $event
        );

        $this->assertEquals(
            'Reunió test',
            $event->titol()
        );

        $this->assertEquals(
            '2026-06-01 10:00:00',
            $event->dataInici()->format('Y-m-d H:i:s')
        );
    }

    public function test_it_rejects_invalid_date_range(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $factory = new AgendaEventFactory();

        $factory->create([
            'titol' => 'Reunió test',
            'descripcio' => 'Desc',
            'tipus' => 'reunio',
            'estat' => 'confirmat',
            'lloc' => 'Barcelona',
            'ciutat_id' => '019e7223-646d-71d1-abee-2ed1613c1d0e',
            'data_inici' => '2026-06-02 10:00:00',
            'data_fi' => '2026-06-01 10:00:00',
            'tot_el_dia' => false,
        ]);
    }
}
