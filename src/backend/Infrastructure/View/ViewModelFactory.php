<?php

declare(strict_types=1);

namespace App\Infrastructure\View;

use App\Infrastructure\Security\Auth\AuthContext;

final class ViewModelFactory
{
    public static function create(): ViewModel
    {
        $user = AuthContext::user();

        return new ViewModel(
            user: $user,
            isAdmin: AuthContext::isAdmin(),
            isAuthenticated: $user !== null,
        );
    }
}
