<?php

declare(strict_types=1);

namespace App\Application\Agenda\UseCase;

use App\Domain\Agenda\Repository\AgendaRepositoryInterface;
use App\Domain\Ciutat\Repository\CiutatRepository;

final class GetAgendaFutureEventsUseCase
{
    public function __construct(
        private AgendaRepositoryInterface $agendaRepository,
        private CiutatRepository $ciutatRepository
    ) {}

    public function execute(): array
    {
        $events = $this->agendaRepository->findFutureEvents();

        $result = [];

        foreach ($events as $event) {

            $ciutatNom = null;

            if ($event->ciutatId() !== null) {
                $ciutat = $this->ciutatRepository->findById(
                    $event->ciutatId()
                );

                $ciutatNom = $ciutat?->getNom();
            }

            $result[] = [
                'id' => $event->id()->toString(),
                'titol' => $event->titol(),
                'descripcio' => $event->descripcio(),
                'tipus' => (string)$event->tipus(),
                'lloc' => $event->lloc(),
                'ciutat_id' => $event->ciutatId()?->toString(),
                'ciutat_nom' => $ciutatNom,
                'data_inici' => $event->dataInici()->format('Y-m-d H:i:s'),
                'data_fi' => $event->dataFi()?->format('Y-m-d H:i:s'),
                'tot_el_dia' => $event->totElDia(),
                'estat' => (string)$event->estat(),
                'creat_el' => $event->creatEl()->format('Y-m-d H:i:s'),
                'actualitzat_el' => $event->actualitzatEl()->format('Y-m-d H:i:s'),
            ];
        }

        return $result;
    }
}
