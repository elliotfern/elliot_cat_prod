<?php

namespace App\Domain\Usuari\ValueObject;

final class Password
{
    public function __construct(
        private string $hash
    ) {
        if (empty($hash)) {
            throw new \InvalidArgumentException('Password hash cannot be empty');
        }
    }

    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    public static function fromPlain(string $plain): self
    {
        return new self(
            password_hash($plain, PASSWORD_BCRYPT)
        );
    }

    public function verify(string $plain): bool
    {
        return password_verify($plain, $this->hash);
    }

    public function hash(): string
    {
        return $this->hash;
    }
}
