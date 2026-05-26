<?php

namespace App\Application\Ciutat\Presenter;

use App\Domain\Ciutat\Entity\Ciutat;

class CiutatResponse
{
    public static function toArray(Ciutat $ciutat): array
    {
        return [
            'id' => $ciutat->getId(),
            'ciutat' => $ciutat->getCiutat(),
            'ciutat_ca' => $ciutat->getCiutatCa(),
            'ciutat_en' => $ciutat->getCiutatEn(),
            'ciutat_final' => $ciutat->getCiutatFinal(),
            'descripcio' => $ciutat->getDescripcio(),

            'pais' => [
                'id' => $ciutat->getPais()->getId(),
                'pais_ca' => $ciutat->getPais()->getPaisCa(),
                'pais_en' => $ciutat->getPais()->getPaisEn(),
            ],

            'created_at' => $ciutat->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $ciutat->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
