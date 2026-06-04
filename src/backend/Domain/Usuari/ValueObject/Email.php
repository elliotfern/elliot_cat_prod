<?php

declare(strict_types=1);

namespace App\Domain\Usuari\ValueObject;

final class Email
{
    public function __construct(
        private string $value
    ) {
        $this->assertValid($value);
    }

    private function assertValid(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email no vàlid');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
