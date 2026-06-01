<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

final class DateRange
{
    public function __construct(
        private readonly DateTimeImmutable $start,
        private readonly ?DateTimeImmutable $end = null
    ) {
        $this->assertValid();
    }

    private function assertValid(): void
    {
        if ($this->end !== null && $this->end < $this->start) {
            throw new InvalidArgumentException('Invalid date range: end < start');
        }
    }

    public function start(): DateTimeImmutable
    {
        return $this->start;
    }

    public function end(): ?DateTimeImmutable
    {
        return $this->end;
    }

    public function contains(DateTimeImmutable $date): bool
    {
        if ($date < $this->start) {
            return false;
        }

        if ($this->end !== null && $date > $this->end) {
            return false;
        }

        return true;
    }

    public function isOpen(): bool
    {
        return $this->end === null;
    }
}
