<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Auth;

use App\Utils\MissatgesAPI;
use App\Utils\Response;

final class AdminMiddleware
{
    public function __construct(
        private AuthGuard $authGuard
    ) {}

    public function handle(): void
    {
        try {

            $this->authGuard->requireAdmin();
        } catch (\RuntimeException $e) {

            Response::error(
                message: MissatgesAPI::error('admin'),
                httpCode: 403
            );
        }
    }
}
