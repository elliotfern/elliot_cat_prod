<?php

namespace App\Domain\Ciutat\Entity;

use App\Domain\Pais\Entity\Pais;

class Ciutat
{
    public function __construct(
        private string $id,
        private string $ciutat,
        private ?string $ciutatCa,
        private ?string $ciutatEn,
        private string $descripcio,
        private Pais $pais,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getCiutat(): string
    {
        return $this->ciutat;
    }

    public function getCiutatCa(): ?string
    {
        return $this->ciutatCa;
    }

    public function getCiutatEn(): ?string
    {
        return $this->ciutatEn;
    }

    public function getDescripcio(): string
    {
        return $this->descripcio;
    }

    public function getPais(): Pais
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
