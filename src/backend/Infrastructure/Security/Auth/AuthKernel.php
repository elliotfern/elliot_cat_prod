<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Auth;

use App\Infrastructure\Security\Auth\AuthFactory;

final class AuthKernel
{
    public static function boot(): void
    {
        try {
            $user = AuthFactory::tryUser();

            AuthContext::set([
                'id'    => $user['user_id'] ?? null,
                'email' => $user['email'] ?? null,
                'role'  => $user['role'] ?? null,
            ]);
        } catch (\Throwable $e) {
            AuthContext::set([
                'id' => null,
                'email' => null,
                'role' => null,
            ]);
        }
    }

    public static function handle(bool $needsAdmin, bool $needsSession): void
    {
        $user = AuthContext::user() ?? [
            'id' => null,
            'role' => null,
        ];

        // no logueado
        if ($needsSession && !$user['id']) {
            throw new UnauthorizedException('Session required');
        }

        // no admin
        if ($needsAdmin && ($user['role'] ?? null) !== 'admin') {
            throw new UnauthorizedException('Admin required');
        }
    }
}
