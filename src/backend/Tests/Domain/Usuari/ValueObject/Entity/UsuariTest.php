<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Usuari\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Usuari\Entity\Usuari;
use App\Domain\Usuari\ValueObject\UserId;
use App\Domain\Usuari\ValueObject\Email;
use App\Domain\Usuari\ValueObject\Password;
use App\Domain\Usuari\Enum\UserRole;
use App\Domain\Usuari\ValueObject\UsuariImgId;

final class UsuariTest extends TestCase
{
    private function createUser(
        string $role = 'user',
        bool $isActive = true
    ): Usuari {
        return new Usuari(
            id: UserId::fromString('12345678-1234-1234-1234-123456789abc'),
            email: new Email('test@test.com'),
            password: Password::fromPlain('secret'),
            nom: 'Test',
            cognom: 'User',
            role: UserRole::from($role),
            isActive: $isActive,
            usuariImgId: null,
            dateCreated: new \DateTimeImmutable(),
            lastLoginAt: null,
            updatedAt: null,
            deletedAt: null,
        );
    }

    public function test_it_detects_admin(): void
    {
        $user = $this->createUser(role: 'admin');

        $this->assertTrue($user->isAdmin());
    }

    public function test_it_detects_non_admin(): void
    {
        $user = $this->createUser(role: 'user');

        $this->assertFalse($user->isAdmin());
    }

    public function test_it_deactivates_user(): void
    {
        $user = $this->createUser();

        $user->deactivate();

        $this->assertFalse($user->isActive());
    }

    public function test_it_activates_user(): void
    {
        $user = $this->createUser(isActive: false);

        $user->activate();

        $this->assertTrue($user->isActive());
    }

    public function test_it_marks_user_as_deleted(): void
    {
        $user = $this->createUser();

        $this->assertFalse($user->isDeleted());

        $user->markAsDeleted();

        $this->assertTrue($user->isDeleted());
    }

    public function test_it_updates_last_login(): void
    {
        $user = $this->createUser();

        $this->assertNull($user->lastLoginAt());

        $user->updateLastLogin();

        $this->assertInstanceOf(\DateTimeImmutable::class, $user->lastLoginAt());
    }
}
