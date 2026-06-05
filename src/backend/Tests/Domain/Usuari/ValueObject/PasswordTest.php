<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Usuari\ValueObject;

use PHPUnit\Framework\TestCase;
use App\Domain\Usuari\ValueObject\Password;

final class PasswordTest extends TestCase
{
    public function test_it_creates_hash_from_plain_password(): void
    {
        $password = Password::fromPlain('secret123');

        $this->assertIsString($password->hash());
        $this->assertNotEmpty($password->hash());
    }

    public function test_it_verifies_correct_password(): void
    {
        $password = Password::fromPlain('secret123');

        $this->assertTrue(
            $password->verify('secret123')
        );
    }

    public function test_it_rejects_wrong_password(): void
    {
        $password = Password::fromPlain('secret123');

        $this->assertFalse(
            $password->verify('wrong-password')
        );
    }

    public function test_it_can_be_created_from_hash(): void
    {
        $hash = password_hash('secret123', PASSWORD_BCRYPT);

        $password = Password::fromHash($hash);

        $this->assertTrue($password->verify('secret123'));
    }

    public function test_it_does_not_allow_empty_hash(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Password::fromHash('');
    }
}
