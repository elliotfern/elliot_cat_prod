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
            'descripcio' => $ciutat->getDescripcio(),

            'pais' => [
                'id' => $ciutat->getPais()->getId(),
                'pais' => $ciutat->getPais()->getPais(),
            ],

            'created_at' => $ciutat->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $ciutat->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
