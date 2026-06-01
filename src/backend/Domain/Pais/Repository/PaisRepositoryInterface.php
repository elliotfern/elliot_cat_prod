<?php

namespace App\Domain\Pais\Repository;

use App\Domain\Pais\Entity\Pais;

interface PaisRepositoryInterface
{
    public function findById(string $id): ?Pais;

    /**
     * @return Pais[]
     */
    public function findAll(): array;

    public function save(Pais $pais): void;
}
