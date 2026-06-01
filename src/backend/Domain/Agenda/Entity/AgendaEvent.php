<?php

declare(strict_types=1);

namespace App\Domain\Agenda\Entity;

use App\Domain\Agenda\ValueObject\AgendaEstat;
use App\Domain\Agenda\ValueObject\AgendaId;
use App\Domain\Agenda\ValueObject\AgendaTipus;
use App\Domain\Ciutat\ValueObject\CiutatId;
use DateTimeImmutable;

final class AgendaEvent
{
    public function __construct(

        private readonly AgendaId $id,
        private readonly string $titol,
        private readonly ?string $descripcio,
        private readonly AgendaTipus $tipus,
        private readonly ?string $lloc,
        private readonly ?CiutatId $ciutatId,
        private readonly DateTimeImmutable $dataInici,
        private readonly ?DateTimeImmutable $dataFi,
        private readonly bool $totElDia,
        private readonly AgendaEstat $estat,
        private readonly DateTimeImmutable $creatEl,
        private readonly DateTimeImmutable $actualitzatEl


    ) {
        $this->assertValidDates();
    }

    public function getId(): AgendaId
    {
        return $this->id;
    }

    public function titol(): string
    {
        return $this->titol;
    }

    public function descripcio(): ?string
    {
        return $this->descripcio;
    }

    public function tipus(): AgendaTipus
    {
        return $this->tipus;
    }

    public function lloc(): ?string
    {
        return $this->lloc;
    }

    public function ciutatId(): ?CiutatId
    {
        return $this->ciutatId;
    }
    public function dataInici(): DateTimeImmutable
    {
        return $this->dataInici;
    }

    public function dataFi(): ?DateTimeImmutable
    {
        return $this->dataFi;
    }

    public function totElDia(): bool
    {
        return $this->totElDia;
    }

    public function estat(): AgendaEstat
    {
        return $this->estat;
    }

    public function creatEl(): DateTimeImmutable
    {
        return $this->creatEl;
    }

    public function actualitzatEl(): DateTimeImmutable
    {
        return $this->actualitzatEl;
    }

    private function assertValidDates(): void
    {
        if ($this->dataFi !== null && $this->dataFi < $this->dataInici) {
            $dataFi = null; // o log + sanitize
        }
    }
}
