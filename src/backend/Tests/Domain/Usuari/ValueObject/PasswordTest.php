<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Usuari\ValueObject;

use PHPUnit\Framework\TestCase;
use App\Domain\Usuari\ValueObject\Password;

final class PasswordTest extends TestCase
{
    public function test_it_can_be_created_from_hash(): void
    {
        $hash = password_hash('secret123', PASSWORD_BCRYPT);

        self::assertIsString($hash);

        $password = Password::fromHash($hash);

        self::assertSame($hash, $password->hash());
    }

    public function test_it_does_not_allow_empty_hash(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Password::fromHash('');
    }

    public function test_it_does_not_allow_whitespace_hash(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Password::fromHash('   ');
    }
}
