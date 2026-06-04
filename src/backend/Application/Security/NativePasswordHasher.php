<?php

declare(strict_types=1);

namespace App\Application\Security;

final class NativePasswordHasher implements PasswordHasher
{
    public function hash(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_DEFAULT, [
            'cost' => 11,
        ]);
    }

    public function verify(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }
}
