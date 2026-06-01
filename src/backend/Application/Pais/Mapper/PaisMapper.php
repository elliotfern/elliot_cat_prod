<?php

namespace App\Application\Pais\Mapper;

use App\Domain\Pais\Entity\Pais;
use App\Application\Pais\DTO\PaisDTO;

class PaisMapper
{
    public static function toDTO(Pais $pais): PaisDTO
    {
        $dto = new PaisDTO();

        $dto->id = $pais->getId();
        $dto->paisCa = $pais->getPaisCa();
        $dto->paisEn = $pais->getPaisEn();

        return $dto;
    }
}
