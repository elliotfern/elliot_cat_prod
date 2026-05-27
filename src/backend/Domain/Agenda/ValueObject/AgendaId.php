<?php

namespace App\Domain\Agenda\ValueObject;

final class AgendaId
{
    public function __construct(
        private string $value // binary(16)
    ) {
        if (strlen($value) !== 16) {
            throw new \InvalidArgumentException('Invalid binary UUID');
        }
    }

    public static function fromString(string $uuid): self
    {
        return new self(
            \Ramsey\Uuid\Uuid::fromString($uuid)->getBytes()
        );
    }

    public function value(): string
    {
        return $this->value;
    }

    public function toString(): string
    {
        return \Ramsey\Uuid\Uuid::fromBytes($this->value)->toString();
    }
}
