<?php

namespace App\Infrastructure\Persistence\Pais;

use App\Domain\Pais\Entity\Pais;
use App\Utils\Uuid;

class MysqlPaisMapper
{
    public static function map(array $row): Pais
    {
        return new Pais(
            uuid::toString($row['id']),
            $row['pais_ca'],
            $row['pais_en'],
            new \DateTimeImmutable($row['created_at']),
            new \DateTimeImmutable($row['updated_at'])
        );
    }
}
