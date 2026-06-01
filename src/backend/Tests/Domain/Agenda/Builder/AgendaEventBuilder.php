<?php

declare(strict_types=1);

namespace App\Tests\Domain\Agenda\Builder;

use App\Domain\Agenda\Entity\AgendaEvent;
use App\Domain\Agenda\ValueObject\AgendaEstat;
use App\Domain\Agenda\ValueObject\AgendaId;
use App\Domain\Ciutat\ValueObject\CiutatId;
use App\Domain\Agenda\ValueObject\AgendaTipus;
use App\Domain\Shared\ValueObject\DateRange;

final class AgendaEventBuilder
{
    private AgendaId $id;

    private string $titol = 'Evento test';
    private ?string $descripcio = 'Descripción test';

    private AgendaTipus $tipus;

    private ?string $lloc = 'Barcelona';
    private ?CiutatId $ciutatId = null;

    private DateRange $dateRange;

    private bool $totElDia = false;

    private AgendaEstat $estat;

    private \DateTimeImmutable $creatEl;
    private \DateTimeImmutable $actualitzatEl;

    public static function new(): self
    {
        $self = new self();

        $self->id = AgendaId::fromString('019e7223-646d-71d1-abee-2ed1613c1d0e');
        $self->tipus = AgendaTipus::fromString('altre');
        $self->estat = AgendaEstat::fromString('confirmat');

        $self->dateRange = new DateRange(
            new \DateTimeImmutable('2026-06-01 10:00:00'),
            new \DateTimeImmutable('2026-06-01 11:00:00')
        );

        $self->creatEl = new \DateTimeImmutable('2026-01-01 10:00:00');
        $self->actualitzatEl = new \DateTimeImmutable('2026-01-01 10:00:00');

        return $self;
    }

    public function withId(string $uuid): self
    {
        $this->id = AgendaId::fromString($uuid);
        return $this;
    }

    public function withCiutat(string $uuid): self
    {
        $this->ciutatId = CiutatId::fromString($uuid);
        return $this;
    }

    public function withTotElDia(bool $value): self
    {
        $this->totElDia = $value;
        return $this;
    }

    public function withTipus(string $tipus): self
    {
        $this->tipus = AgendaTipus::fromString($tipus);
        return $this;
    }

    public function withEstat(string $estat): self
    {
        $this->estat = AgendaEstat::fromString($estat);
        return $this;
    }

    public function withDateRange(string $start, ?string $end): self
    {
        $this->dateRange = new DateRange(
            new \DateTimeImmutable($start),
            $end ? new \DateTimeImmutable($end) : null
        );

        return $this;
    }


    public function withDescripcio(?string $descripcio): self
    {
        $this->descripcio = $descripcio;
        return $this;
    }

    public function withLloc(?string $lloc): self
    {
        $this->lloc = $lloc;
        return $this;
    }

    public function build(): AgendaEvent
    {
        return new AgendaEvent(
            $this->id,
            $this->titol,
            $this->descripcio,
            $this->tipus,
            $this->lloc,
            $this->ciutatId,
            $this->dateRange,
            $this->totElDia,
            $this->estat,
            $this->creatEl,
            $this->actualitzatEl
        );
    }
}
