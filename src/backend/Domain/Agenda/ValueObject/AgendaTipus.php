<?php

declare(strict_types=1);

namespace App\Domain\Agenda\ValueObject;

use InvalidArgumentException;

final class AgendaTipus
{
    private const ALLOWED = [
        'reunio',
        'visita_medica',
        'videotrucada',
        'viatge',
        'altre',
        'aniversari',
    ];

    public function __construct(
        private readonly string $value
    ) {
        if (!in_array($value, self::ALLOWED, true)) {
            throw new InvalidArgumentException(
                'Tipus d\'agenda invàlid'
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

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
