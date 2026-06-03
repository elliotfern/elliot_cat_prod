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
        public string $source // 'agenda' | 'birthday'
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'titol' => $this->titol,
            'tipus' => $this->tipus,
            'data_inici' => $this->dataInici,
            'data_fi' => $this->dataFi,
            'tot_el_dia' => $this->totElDia,
            'lloc' => $this->lloc,
            'source' => $this->source,
        ];
    }
}
