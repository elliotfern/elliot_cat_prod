<?php

namespace App\Domain\Client\ValueObject;

final class ClientId
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
