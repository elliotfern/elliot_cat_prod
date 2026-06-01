<?php

namespace App\Domain\Agenda\ValueObject;

use App\Utils\Uuid;

final class AgendaId
{
    public function __construct(
        private string $value
    ) {
        if (strlen($value) !== 16) {
            throw new \InvalidArgumentException('Invalid binary UUID');
        }
    }

    public static function generate(): self
    {
        return new self(
            Uuid::toBinary(
                Uuid::generate()
            )
        );
    }

    public static function fromString(string $uuid): self
    {
        return new self(
            Uuid::toBinary($uuid)
        );
    }

    public function value(): string
    {
        return $this->value;
    }

    public function toString(): string
    {
        return Uuid::toString($this->value);
    }
}
