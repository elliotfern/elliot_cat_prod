<?php

declare(strict_types=1);

namespace App\Domain\Agenda\ValueObject;

use InvalidArgumentException;

final class AgendaEstat
{
    private const ALLOWED = [
        'pendent',
        'confirmat',
        'cancel·lat',
    ];

    public function __construct(
        private readonly string $value
    ) {
        if (!in_array($value, self::ALLOWED, true)) {
            throw new InvalidArgumentException(
                'Estat d\'agenda invàlid'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function allowed(): array
    {
        return self::ALLOWED;
    }
}
