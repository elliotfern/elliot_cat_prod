<?php

declare(strict_types=1);

namespace App\Infrastructure\View;

final class ViewModel
{
    public function __construct(
        public readonly ?array $user,
        public readonly bool $isAdmin,
        public readonly bool $isAuthenticated,
    ) {}
}
