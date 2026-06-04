<?php

declare(strict_types=1);

namespace App\Application\Usuari\UseCase;

use App\Infrastructure\Security\Jwt\JwtService;

final class MeUsuariUseCase
{
    public function __construct(
        private JwtService $jwtService
    ) {}

    public function execute(string $token): array
    {
        if (!$token) {
            throw new \RuntimeException('Missing token');
        }

        $payload = $this->jwtService->decode($token);

        return [
            'authenticated' => true,
            'user' => [
                'id' => $payload['user_id'] ?? null,
                'email' => $payload['email'] ?? null,
                'full_name' => $payload['full_name'] ?? null,
                'role' => $payload['user_type'] ?? $payload['role'] ?? null,
            ]
        ];
    }
}
