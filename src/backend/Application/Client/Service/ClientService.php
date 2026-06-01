<?php

namespace App\Application\Client\Service;

use App\Domain\Client\Entity\Client;
use App\Domain\Client\Repository\ClientRepositoryInterface;
use App\Domain\Client\ValueObject\ClientId;
use App\Utils\Uuid;

final class ClientService
{
    public function __construct(
        private ClientRepositoryInterface $repository
    ) {}

    public function getById(string $id): ?Client
    {
        return $this->repository->findById(
            new ClientId(Uuid::toBinary($id))
        );
    }

    public function getAll(): array
    {
        return $this->repository->findAll();
    }
}
