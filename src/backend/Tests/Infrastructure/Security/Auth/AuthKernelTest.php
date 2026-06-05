<?php

declare(strict_types=1);

namespace Tests\Integration\Security\Auth;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Security\Auth\AuthKernel;
use App\Infrastructure\Security\Auth\AuthContext;
use App\Infrastructure\Security\Auth\AuthGuard;
use App\Infrastructure\Security\Jwt\JwtDecoderInterface;
use PHPUnit\Framework\MockObject\MockObject;

final class AuthKernelTest extends TestCase
{
    private JwtDecoderInterface&MockObject $jwt;

    protected function setUp(): void
    {
        AuthContext::set(null);

        $this->jwt = $this->createMock(JwtDecoderInterface::class);
    }

    public function test_admin_can_access_gestio(): void
    {
        AuthContext::set([
            'id' => '1',
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        $this->expectNotToPerformAssertions();

        AuthKernel::handle(true, false);
    }

    public function test_user_without_token_is_blocked_for_admin_route(): void
    {
        AuthContext::set([
            'id' => null,
            'email' => null,
            'role' => null,
        ]);

        $this->expectException(\App\Infrastructure\Security\Auth\UnauthorizedException::class);

        AuthKernel::handle(true, false);
    }
}
