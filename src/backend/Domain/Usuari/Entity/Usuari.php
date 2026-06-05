<?php

declare(strict_types=1);

namespace App\Domain\Usuari\Entity;

use App\Domain\Usuari\Enum\UserRole;
use App\Domain\Usuari\ValueObject\Email;
use App\Domain\Usuari\ValueObject\Password;
use App\Domain\Usuari\ValueObject\UserId;
use App\Domain\Usuari\ValueObject\UsuariImgId;

final class Usuari
{
    public function __construct(
        private readonly UserId $id,
        private Email $email,
        private Password $password,
        private string $nom,
        private string $cognom,
        private UserRole $role,
        private bool $isActive,
        private ?UsuariImgId $usuariImgId,
        private \DateTimeImmutable $dateCreated,
        private ?\DateTimeImmutable $lastLoginAt,
        private ?\DateTimeImmutable $updatedAt,
        private ?\DateTimeImmutable $deletedAt,
    ) {}

    // =========================
    // GETTERS (domini pur)
    // =========================

    public function id(): UserId
    {
        return $this->id;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function password(): Password
    {
        return $this->password;
    }

    public function nom(): string
    {
        return $this->nom;
    }

    public function cognom(): string
    {
        return $this->cognom;
    }

    public function role(): UserRole
    {
        return $this->role;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function usuariImgId(): ?UsuariImgId
    {
        return $this->usuariImgId;
    }

    public function dateCreated(): \DateTimeImmutable
    {
        return $this->dateCreated;
    }

    public function lastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function deletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    // =========================
    // DOMINI (petites regles)
    // =========================

    public function isAdmin(): bool
    {
        return $this->role->isAdmin();
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function activate(): void
    {
        $this->isActive = true;
        $this->touch();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->touch();
    }

    public function markAsDeleted(): void
    {
        $this->deletedAt = new \DateTimeImmutable();
    }

    public function updateLastLogin(): void
    {
        $this->lastLoginAt = new \DateTimeImmutable();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
