<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Auth;

use App\Infrastructure\Security\Auth\AuthFactory;

final class AuthKernel

{
    public static function handle(bool $needsAdmin, bool $needsSession): void
    {
        $user = AuthContext::user();

        // no logueado
        if ($needsSession && !$user['id']) {
            header('Location: /entrada', true, 302);
            exit;
        }

        // no admin
        if ($needsAdmin && ($user['role'] ?? null) !== 'admin') {
            header('Location: /entrada', true, 302);
            exit;
        }
    }

    public static function boot(): void
    {
        try {
            $user = AuthFactory::tryUser(); // o requireAuth()

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
}
