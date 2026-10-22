<?php

declare(strict_types=1);

namespace App\Application\Usuari\UseCase;

use App\Application\Security\PasswordHasher;
use App\Domain\Usuari\Entity\Usuari;
use App\Domain\Usuari\Enum\UserRole;
use App\Domain\Usuari\Repository\UsuariRepository;
use App\Domain\Usuari\ValueObject\Email;
use App\Domain\Usuari\ValueObject\Password;
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
        string $cognom,
        bool $acceptaPrivacitat
    ): Usuari {
        $nom = trim($nom);
        $cognom = trim($cognom);

        if ($nom === '') {
            throw new \InvalidArgumentException(
                'El nom és obligatori.'
            );
        }

        if ($cognom === '') {
            throw new \InvalidArgumentException(
                'Els cognoms són obligatoris.'
            );
        }

        if (strlen($password) < 12) {
            throw new \InvalidArgumentException(
                'La contrasenya ha de tenir almenys 12 caràcters.'
            );
        }

        if (!$acceptaPrivacitat) {
            throw new \InvalidArgumentException(
                'Cal acceptar la política de privacitat.'
            );
        }

        $emailValue = new Email($email);

        if ($this->repository->findByEmail($emailValue) !== null) {
            throw new \InvalidArgumentException('email_duplicat');
        }

        $hashedPassword = Password::fromHash(
            $this->passwordHasher->hash($password)
        );

        $now = new \DateTimeImmutable();

        $usuari = new Usuari(
            id: new UserId(Uuid::generateBinary()),
            email: $emailValue,
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
