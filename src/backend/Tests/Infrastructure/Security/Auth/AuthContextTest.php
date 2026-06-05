<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Security\Auth;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Security\Auth\AuthContext;

final class AuthContextTest extends TestCase
{
    protected function setUp(): void
    {
        AuthContext::set(null);
    }

    public function test_it_stores_user(): void
    {
        $user = [
            'id' => '019710e4-90e4',
            'email' => 'test@test.com',
            'role' => 'admin',
        ];

        AuthContext::set($user);

        $this->assertSame($user, AuthContext::user());
    }

    public function test_it_detects_admin_user(): void
    {
        AuthContext::set([
            'role' => 'admin',
        ]);

        $this->assertTrue(AuthContext::isAdmin());
    }

    public function test_it_detects_non_admin_user(): void
    {
        AuthContext::set([
            'role' => 'user',
        ]);

        $this->assertFalse(AuthContext::isAdmin());
    }

    public function test_guest_is_not_admin(): void
    {
        AuthContext::set(null);

        $this->assertFalse(AuthContext::isAdmin());
    }
}
