<?php

namespace App\Application\Pais\Presenter;

use App\Domain\Pais\Entity\Pais;

class PaisResponse
{
    public static function toArray(Pais $pais): array
    {
        return [
            'id' => $pais->getId(),
            'pais' => $pais->getPais(),
        ];
    }
}
