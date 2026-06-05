<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Auth;

use App\Infrastructure\Security\Jwt\JwtDecoderInterface;

final class AuthGuard
{
    public function __construct(
        private JwtDecoderInterface $jwtService

    ) {}

    public function requireAuth(): array
    {
        $token = $_COOKIE['token'] ?? null;

        if (!$token) {
            throw new \RuntimeException('Missing token');
        }

        $decoded = $this->jwtService->decode($token);
        return $decoded;
    }

    public function requireAdmin(): array
    {
        $payload = $this->requireAuth();

        $role = $payload['role'] ?? null;

        if ($role !== 'admin') {
            throw new \RuntimeException('Admin access required');
        }

        return $payload;
    }
}
