<?php

declare(strict_types=1);

namespace App\Application\Agenda\UseCase;

use App\Domain\Agenda\Repository\AgendaRepositoryInterface;
use App\Domain\Agenda\ValueObject\AgendaId;
use App\Domain\Ciutat\Repository\CiutatRepository;
use App\Utils\Uuid;

final class GetAgendaByIdUseCase
{
    public function __construct(
        private AgendaRepositoryInterface $repository,
        private CiutatRepository $ciutatRepository
    ) {}

    public function execute(AgendaId $id): ?array
    {
        $event = $this->repository->findById($id);

        if (!$event) {
            return null;
        }

        $ciutatNom = null;

        if ($event->ciutatId()) {

            $ciutat = $this->ciutatRepository->findById(
                $event->ciutatId()->toString()
            );

            $ciutatNom = $ciutat?->getNom();
        }

        return [
            'id' => $event->getId()->toString(),
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
}
