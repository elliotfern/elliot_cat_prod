<?php

declare(strict_types=1);

namespace App\Domain\Usuari\ValueObject;

final class Password
{
    private function __construct(
        private readonly string $hash
    ) {
        if (trim($hash) === '') {
            throw new \InvalidArgumentException(
                'Password hash cannot be empty'
            );
        }
    }

    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    public function hash(): string
    {
        return $this->hash;
    }
}
