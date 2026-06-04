<?php

declare(strict_types=1);

namespace App\Domain\Usuari\Repository;

use App\Domain\Usuari\Entity\Usuari;
use App\Domain\Usuari\ValueObject\Email;
use App\Domain\Usuari\ValueObject\UserId;

interface UsuariRepository
{
    public function save(Usuari $usuari): void;

    public function findById(UserId $id): ?Usuari;

    public function findByEmail(Email $email): ?Usuari;

    public function delete(UserId $id): void;
}
