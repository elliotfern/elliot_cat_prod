<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Security;

use App\Application\Security\NativePasswordHasher;
use PHPUnit\Framework\TestCase;

final class NativePasswordHasherTest extends TestCase
{
    private NativePasswordHasher $hasher;

    protected function setUp(): void
    {
        $this->hasher = new NativePasswordHasher();
    }

    public function test_it_creates_hash_from_plain_password(): void
    {
        $hash = $this->hasher->hash('secret123');

        self::assertNotSame('secret123', $hash);
        self::assertNotEmpty($hash);
        self::assertTrue(password_verify('secret123', $hash));
    }

    public function test_it_verifies_correct_password(): void
    {
        $hash = $this->hasher->hash('secret123');

        self::assertTrue(
            $this->hasher->verify('secret123', $hash)
        );
    }

    public function test_it_rejects_wrong_password(): void
    {
        $hash = $this->hasher->hash('secret123');

        self::assertFalse(
            $this->hasher->verify('wrong-password', $hash)
        );
    }
}
