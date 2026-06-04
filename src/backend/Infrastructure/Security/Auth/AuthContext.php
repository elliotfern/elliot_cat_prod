<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Auth;

final class AuthContext
{
    private static ?array $user = null;

    public static function set(?array $user): void
    {
        self::$user = $user;
    }

    public static function user(): ?array
    {
        return self::$user;
    }

    public static function isAdmin(): bool
    {
        return (self::$user['role'] ?? null) === 'admin';
    }
}
