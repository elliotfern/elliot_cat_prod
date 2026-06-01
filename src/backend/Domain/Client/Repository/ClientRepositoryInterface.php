<?php

namespace App\Domain\Client\Repository;

use App\Domain\Client\Entity\Client;
use App\Domain\Client\ValueObject\ClientId;

interface ClientRepositoryInterface
{
    public function findById(ClientId $id): ?Client;

    public function findAll(): array;

    public function save(Client $client): void;
}
