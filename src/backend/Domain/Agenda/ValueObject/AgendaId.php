<?php

namespace App\Domain\Agenda\ValueObject;

final class AgendaId
{
    public function __construct(
        private string $value
    ) {
        if (strlen($value) !== 16) {
            throw new \InvalidArgumentException('Invalid binary UUID');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
