<?php

declare(strict_types=1);

namespace App\Tests\Domain\Agenda\ValueObject;

use PHPUnit\Framework\TestCase;
use App\Domain\Agenda\ValueObject\AgendaId;

final class AgendaIdTest extends TestCase
{
    public function test_it_accepts_binary_uuid(): void
    {
        $uuid = '019e7223-646d-71d1-abee-2ed1613c1d0e';

        $binary = AgendaId::fromString($uuid)->value();

        $id = new AgendaId($binary);

        $this->assertInstanceOf(AgendaId::class, $id);

        $this->assertEquals(16, strlen($id->value()));
    }

    public function test_it_rejects_invalid_binary_uuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new AgendaId('invalid');
    }

    public function test_from_string_creates_valid_binary_uuid(): void
    {
        $uuid = '019e7223-646d-71d1-abee-2ed1613c1d0e';

        $id = AgendaId::fromString($uuid);

        $this->assertInstanceOf(AgendaId::class, $id);

        $this->assertEquals(16, strlen($id->value()));
    }

    public function test_to_string_returns_same_uuid(): void
    {
        $uuid = '019e7223-646d-71d1-abee-2ed1613c1d0e';

        $id = AgendaId::fromString($uuid);

        $this->assertEquals($uuid, $id->toString());
    }

    public function test_round_trip_conversion_is_consistent(): void
    {
        $uuid = '019e7223-646d-71d1-abee-2ed1613c1d0e';

        $id1 = AgendaId::fromString($uuid);
        $id2 = AgendaId::fromString($id1->toString());

        $this->assertEquals($id1->toString(), $id2->toString());
    }
}
