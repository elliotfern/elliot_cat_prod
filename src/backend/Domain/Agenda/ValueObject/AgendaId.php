<?php

declare(strict_types=1);

namespace App\Domain\Agenda\ValueObject;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid as ramsey;
use App\Utils\Uuid;

final class AgendaId
{
    public function __construct(
        private readonly string $value
    ) {
        if (!ramsey::isValid($value)) {
            throw new InvalidArgumentException('UUID invàlid');
        }
    }

    public static function generate(): self
    {
        return new self(
            ramsey::uuid7()->toString()
        );
    }

    public static function fromBinary(string $binary): self
    {
        if (strlen($binary) !== 16) {
            throw new InvalidArgumentException(
                'UUID binary inválido (esperado 16 bytes, recibido ' . strlen($binary) . ')'
            );
        }

        return new self(
            Uuid::toString($binary)
        );
    }

    public function value(): string
    {
        return $this->value;
    }

    public function toBinary(): string
    {
        $binary = Uuid::toBinary($this->value);

        if (strlen($binary) !== 16) {
            throw new InvalidArgumentException('UUID conversion failed');
        }

        return $binary;
    }

    public function toString(): string
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
}
