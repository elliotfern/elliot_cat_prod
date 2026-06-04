<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Auth;

use App\Utils\MissatgesAPI;
use App\Utils\Response;

final class AuthMiddleware
{
    public function __construct(
        private AuthGuard $authGuard
    ) {}

    public function handle(): void
    {
        try {

            $user = $this->authGuard->requireAuth();

            AuthContext::set([
                'id' => $user['user_id'] ?? null,
                'email' => $user['email'] ?? null,
                'role' => $user['role'] ?? null,
            ]);
        } catch (\RuntimeException $e) {

            Response::error(
                message: MissatgesAPI::error('login'),
                httpCode: 401
            );
        }
    }
}
