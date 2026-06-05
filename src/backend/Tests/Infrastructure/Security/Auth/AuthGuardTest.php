<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Security\Auth;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Security\Auth\AuthGuard;
use App\Infrastructure\Security\Jwt\JwtDecoderInterface;
use App\Infrastructure\Security\Jwt\JwtService;
use PHPUnit\Framework\MockObject\MockObject;

final class AuthGuardTest extends TestCase
{
    private JwtDecoderInterface|MockObject $jwtService;
    private AuthGuard $authGuard;

    protected function setUp(): void
    {
        $this->jwtService = $this->createMock(JwtDecoderInterface::class);


        $this->authGuard = new AuthGuard(
            $this->jwtService
        );

        unset($_COOKIE['token']);
    }

    public function test_it_requires_auth(): void
    {
        $_COOKIE['token'] = 'fake-token';

        $payload = [
            'user_id' => '123',
            'role' => 'user',
        ];

        $this->jwtService
            ->expects($this->once())
            ->method('decode')
            ->with('fake-token')
            ->willReturn($payload);

        $result = $this->authGuard->requireAuth();

        $this->assertSame($payload, $result);
    }

    public function test_it_throws_exception_when_token_missing(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->authGuard->requireAuth();
    }

    public function test_it_allows_admin_user(): void
    {
        $_COOKIE['token'] = 'admin-token';

        $payload = [
            'user_id' => '123',
            'role' => 'admin',
        ];

        $this->jwtService
            ->method('decode')
            ->willReturn($payload);

        $result = $this->authGuard->requireAdmin();

        $this->assertSame($payload, $result);
    }

    public function test_it_blocks_non_admin_user(): void
    {
        $_COOKIE['token'] = 'user-token';

        $payload = [
            'user_id' => '123',
            'role' => 'user',
        ];

        $this->jwtService
            ->method('decode')
            ->willReturn($payload);

        $this->expectException(\RuntimeException::class);

        $this->authGuard->requireAdmin();
    }
}
