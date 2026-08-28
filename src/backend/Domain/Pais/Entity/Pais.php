<?php

namespace App\Domain\Pais\Entity;

class Pais
{
    public function __construct(
        private string $id,
        private string $pais,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getPais(): string
    {
        return $this->pais;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
