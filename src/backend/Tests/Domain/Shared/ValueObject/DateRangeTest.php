<?php

declare(strict_types=1);

namespace App\Tests\Domain\Shared\ValueObject;

use PHPUnit\Framework\TestCase;
use App\Domain\Shared\ValueObject\DateRange;
use InvalidArgumentException;

final class DateRangeTest extends TestCase
{
    public function test_it_accepts_valid_range(): void
    {
        $start = new \DateTimeImmutable('2026-06-01 10:00:00');
        $end   = new \DateTimeImmutable('2026-06-01 11:00:00');

        $range = new DateRange($start, $end);

        $this->assertSame($start, $range->start());
        $this->assertSame($end, $range->end());
    }

    public function test_it_allows_open_ended_range(): void
    {
        $start = new \DateTimeImmutable('2026-06-01 10:00:00');

        $range = new DateRange($start, null);

        $this->assertSame($start, $range->start());
        $this->assertNull($range->end());
    }

    public function test_it_rejects_end_before_start(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $start = new \DateTimeImmutable('2026-06-02 10:00:00');
        $end   = new \DateTimeImmutable('2026-06-01 10:00:00');

        new DateRange($start, $end);
    }
}
