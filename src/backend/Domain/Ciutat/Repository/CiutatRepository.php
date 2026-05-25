<?php

namespace App\Domain\Ciutat\Repository;

use App\Domain\Ciutat\Entity\Ciutat;

interface CiutatRepository
{
    public function getAll(): array;

    public function findById(string $id): Ciutat;
}
