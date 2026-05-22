<?php

namespace App\Domain\Client\Entity;

use App\Domain\Client\ValueObject\ClientId;
use App\Domain\Client\ValueObject\Email;

final class Client
{
    public function __construct(
        private ClientId $id,
        private string $nom,
        private ?string $cognoms,
        private Email $email,
        private ?string $web,
        private ?string $nif,
        private ?string $empresa,
        private string $adreca,
        private ?string $cp,
        private string $ciutatId,
        private string $provinciaId,
        private string $paisId,
        private ?string $telefon,
        private string $estatId,
        private \DateTimeImmutable $registre
    ) {}

    public function id(): ClientId
    {
        return $this->id;
    }

    public function nom(): string
    {
        return $this->nom;
    }

    public function cognoms(): ?string
    {
        return $this->cognoms;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function web(): ?string
    {
        return $this->web;
    }

    public function nif(): ?string
    {
        return $this->nif;
    }

    public function empresa(): ?string
    {
        return $this->empresa;
    }

    public function adreca(): string
    {
        return $this->adreca;
    }

    public function cp(): ?string
    {
        return $this->cp;
    }

    public function ciutatId(): string
    {
        return $this->ciutatId;
    }

    public function provinciaId(): string
    {
        return $this->provinciaId;
    }

    public function paisId(): string
    {
        return $this->paisId;
    }

    public function telefon(): ?string
    {
        return $this->telefon;
    }

    public function estatId(): string
    {
        return $this->estatId;
    }

    public function registre(): \DateTimeImmutable
    {
        return $this->registre;
    }

    public function fullName(): string
    {
        return trim($this->nom . ' ' . ($this->cognoms ?? ''));
    }

    public function isEmpresa(): bool
    {
        return !empty($this->empresa);
    }
}
