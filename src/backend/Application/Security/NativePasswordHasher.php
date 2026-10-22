<?php

declare(strict_types=1);

namespace App\Application\Security;

final class NativePasswordHasher implements PasswordHasher
{
    public function hash(string $plainPassword): string
    {
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT, [
            'cost' => 11,
        ]);

        if ($hash === false) {
            throw new \RuntimeException(
                'No s\'ha pogut generar el hash de la contrasenya.'
            );
        }

        return $hash;
    }

    public function verify(
        string $plainPassword,
        string $hashedPassword
    ): bool {
        return password_verify($plainPassword, $hashedPassword);
    }
}
