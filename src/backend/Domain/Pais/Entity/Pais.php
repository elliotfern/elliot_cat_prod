<?php

namespace App\Domain\Pais\Entity;

class Pais
{
    public function __construct(
        private string $id,
        private string $paisCa,
        private ?string $paisEn,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getPaisCa(): string
    {
        return $this->paisCa;
    }

    public function getPaisEn(): ?string
    {
        return $this->paisEn;
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
