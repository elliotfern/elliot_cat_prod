<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Auth;

use App\Infrastructure\Security\Jwt\JwtService;

final class AuthFactory
{
    private static function guard(): AuthGuard
    {
        $jwtSecret = $_ENV['TOKEN'] ?? null;

        if (!$jwtSecret) {
            throw new \RuntimeException('Missing JWT secret');
        }

        $jwtService = new JwtService($jwtSecret);

        return new AuthGuard($jwtService);
    }

    public static function auth(): AuthMiddleware
    {
        return new AuthMiddleware(self::guard());
    }

    public static function admin(): AdminMiddleware
    {
        return new AdminMiddleware(self::guard());
    }

    public static function tryUser(): ?array
    {
        try {
            return self::guard()->requireAuth();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
