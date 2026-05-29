<?php

declare(strict_types=1);

namespace App\Tests\Domain\Agenda\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Agenda\Entity\AgendaEvent;
use App\Tests\Domain\Agenda\Builder\AgendaEventBuilder;
use App\Domain\Agenda\ValueObject\AgendaTipus;
use InvalidArgumentException;

final class AgendaEventTest extends TestCase
{
    public function test_it_creates_valid_event(): void
    {
        $event = AgendaEventBuilder::new()->build();

        $this->assertInstanceOf(AgendaEvent::class, $event);
        $this->assertEquals('Evento test', $event->titol());
    }

    public function test_it_has_correct_type(): void
    {
        $event = AgendaEventBuilder::new()->withTipus('viatge')->build();

        $this->assertEquals(
            'viatge',
            (string) $event->tipus()
        );
    }

    public function test_it_allows_null_city(): void
    {
        $event = AgendaEventBuilder::new()->build();

        $this->assertNull($event->ciutatId());
    }

    public function test_it_rejects_invalid_date_range(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AgendaEventBuilder::new()
            ->withDataInici('2026-06-02 10:00:00')
            ->withDataFi('2026-06-01 10:00:00')
            ->build();
    }

    public function test_it_can_be_all_day_event(): void
    {
        $event = AgendaEventBuilder::new()
            ->withTotElDia(true)
            ->build();

        $this->assertTrue($event->totElDia());
    }

    public function test_it_handles_birthdays_like_events(): void
    {
        $event = AgendaEventBuilder::new()
            ->withTipus('aniversari')
            ->withTotElDia(true)
            ->build();

        $this->assertEquals('aniversari', (string) $event->tipus());
        $this->assertTrue($event->totElDia());
    }
}
