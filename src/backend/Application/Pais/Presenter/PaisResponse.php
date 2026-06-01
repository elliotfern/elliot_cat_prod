<?php

namespace App\Application\Pais\Presenter;

use App\Domain\Pais\Entity\Pais;

class PaisResponse
{
    public static function toArray(Pais $pais): array
    {
        return [
            'id' => $pais->getId(),
            'pais_ca' => $pais->getPaisCa(),
            'pais_en' => $pais->getPaisEn(),
        ];
    }
}
