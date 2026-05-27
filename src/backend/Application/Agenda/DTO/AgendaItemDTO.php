<?php

declare(strict_types=1);

namespace App\Application\Agenda\DTO;

final class AgendaItemDTO
{
    public function __construct(
        public string $id,
        public string $titol,
        public string $tipus,
        public string $dataInici,
        public ?string $dataFi,
        public bool $totElDia,
        public ?string $lloc,
        public ?string $ciutatNom,
        public string $source // 'agenda' | 'birthday'
    ) {}
}
