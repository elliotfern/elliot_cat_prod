<?php

declare(strict_types=1);

namespace App\Application\Usuari\UseCase;

use App\Application\Security\PasswordHasher;
use App\Domain\Usuari\Entity\Usuari;
use App\Domain\Usuari\Enum\UserRole;
use App\Domain\Usuari\Repository\UsuariRepository;
use App\Domain\Usuari\ValueObject\Email;
use App\Domain\Usuari\ValueObject\UserId;
use App\Utils\Uuid;

final class CreateUsuariUseCase
{
    public function __construct(
        private UsuariRepository $repository,
        private PasswordHasher $passwordHasher
    ) {}

    public function execute(
        string $email,
        string $password,
        string $nom,
        string $cognom
    ): Usuari {
        $now = new \DateTimeImmutable();
        $hashedPassword = $this->passwordHasher->hash($password);

        $usuari = new Usuari(
            id: new UserId(Uuid::generateBinary()),
            email: new Email($email),
            password: $hashedPassword,
            nom: $nom,
            cognom: $cognom,
            role: UserRole::USER,
            isActive: true,
            usuariImgId: null,
            dateCreated: $now,
            lastLoginAt: null,
            updatedAt: null,
            deletedAt: null,
        );

        $this->repository->save($usuari);

        return $usuari;
    }
}
