<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Usuari;

use App\Domain\Usuari\Entity\Usuari;
use App\Domain\Usuari\Repository\UsuariRepository;
use App\Domain\Usuari\ValueObject\Email;
use App\Domain\Usuari\ValueObject\UserId;
use App\Domain\Usuari\ValueObject\UsuariImgId;
use App\Domain\Usuari\Enum\UserRole;
use App\Domain\Usuari\ValueObject\Password;
use PDO;

final class MysqlUsuariRepository implements UsuariRepository
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function save(Usuari $usuari): void
    {
        $sql = "
            INSERT INTO db_usuaris (
                id, email, password, nom, cognom, role,
                isActive, usuari_img_id, dateCreated, lastLoginAt,
                updatedAt, deletedAt
            ) VALUES (
                :id, :email, :password, :nom, :cognom, :role,
                :isActive, :usuari_img_id, :dateCreated, :lastLoginAt,
                :updatedAt, :deletedAt
            )
            ON DUPLICATE KEY UPDATE
                email = VALUES(email),
                password = VALUES(password),
                nom = VALUES(nom),
                cognom = VALUES(cognom),
                role = VALUES(role),
                isActive = VALUES(isActive),
                usuari_img_id = VALUES(usuari_img_id),
                lastLoginAt = VALUES(lastLoginAt),
                updatedAt = VALUES(updatedAt),
                deletedAt = VALUES(deletedAt)
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'id' => $usuari->id()->value(),
            'email' => $usuari->email()->value(),
            'password' => $usuari->password(),
            'nom' => $usuari->nom(),
            'cognom' => $usuari->cognom(),
            'role' => $usuari->role()->value,
            'isActive' => (int) $usuari->isActive(),
            'usuari_img_id' => $usuari->usuariImgId()?->value(),
            'dateCreated' => $usuari->dateCreated()->format('Y-m-d H:i:s'),
            'lastLoginAt' => $usuari->lastLoginAt()?->format('Y-m-d H:i:s'),
            'updatedAt' => $usuari->updatedAt()?->format('Y-m-d H:i:s'),
            'deletedAt' => $usuari->deletedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(UserId $id): ?Usuari
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM db_usuaris WHERE id = :id AND deletedAt IS NULL
        ");

        $stmt->execute([
            'id' => $id->value()
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->map($row) : null;
    }

    public function findByEmail(Email $email): ?Usuari
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM db_usuaris WHERE email = :email AND deletedAt IS NULL
        ");

        $stmt->execute([
            'email' => $email->value()
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->map($row) : null;
    }

    public function delete(UserId $id): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE db_usuaris
            SET deletedAt = :deletedAt
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id->value(),
            'deletedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
        ]);
    }

    private function map(array $row): Usuari
    {
        return new Usuari(
            id: new UserId($row['id']),
            email: new Email($row['email']),
            password: Password::fromHash($row['password']),
            nom: $row['nom'],
            cognom: $row['cognom'],
            role: UserRole::from($row['role']),
            isActive: (bool) $row['isActive'],
            usuariImgId: $row['usuari_img_id']
                ? new UsuariImgId($row['usuari_img_id'])
                : null,
            dateCreated: new \DateTimeImmutable($row['dateCreated']),
            lastLoginAt: $row['lastLoginAt']
                ? new \DateTimeImmutable($row['lastLoginAt'])
                : null,
            updatedAt: $row['updatedAt']
                ? new \DateTimeImmutable($row['updatedAt'])
                : null,
            deletedAt: $row['deletedAt']
                ? new \DateTimeImmutable($row['deletedAt'])
                : null,
        );
    }
}
