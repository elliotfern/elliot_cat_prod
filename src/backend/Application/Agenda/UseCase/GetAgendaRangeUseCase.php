<?php

declare(strict_types=1);

namespace App\Application\Agenda\UseCase;

use App\Application\Agenda\DTO\AgendaItemDTO;
use App\Application\Agenda\Service\BirthdayService;
use App\Domain\Agenda\Repository\AgendaRepositoryInterface;
use App\Domain\Ciutat\Repository\CiutatRepository;



final class GetAgendaRangeUseCase
{
    public function __construct(
        private AgendaRepositoryInterface $agendaRepository,
        private BirthdayService $birthdayService
    ) {}

    public function execute(string $from, string $to): array
    {
        $fromDate = new \DateTimeImmutable($from);
        $toDate   = new \DateTimeImmutable($to);

        // eventos reales
        $events = $this->agendaRepository->findByDateRange($fromDate, $toDate);

        $result = [];

        foreach ($events as $event) {

            $result[] = new AgendaItemDTO(
                id: $event->getId()->toString(),
                titol: $event->titol(),
                tipus: (string)$event->tipus(),
                dataInici: $event->dataInici()->format('Y-m-d H:i:s'),
                dataFi: $event->dataFi()?->format('Y-m-d H:i:s'),
                totElDia: $event->totElDia(),
                lloc: $event->lloc(),
                source: 'agenda'
            );
        }

        // cumpleaños
        $birthdaysRaw = $this->birthdayService->getBetween($from, $to);

        $birthdays = [];

        foreach ($birthdaysRaw as $b) {

            $birthdays[] = new AgendaItemDTO(
                id: $b['id'], // asumir ya UUID string válido
                titol: $b['titol'],
                tipus: $b['tipus'],
                dataInici: $b['data_inici'],
                dataFi: $b['data_fi'],
                totElDia: true,
                lloc: null,
                source: 'birthday'
            );
        }

        // merge
        $all = array_merge($result, $birthdays);

        usort(
            $all,
            fn(AgendaItemDTO $a, AgendaItemDTO $b) =>
            strcmp($a->dataInici, $b->dataInici)
        );

        return $all;
    }
}
