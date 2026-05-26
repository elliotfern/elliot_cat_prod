<?php

namespace App\Infrastructure\Persistence\Ciutat;

use App\Domain\Ciutat\Entity\Ciutat;
use App\Domain\Pais\Entity\Pais;
use App\Utils\Uuid;

class MysqlCiutatMapper
{
    public static function map(array $row): Ciutat
    {
        $pais = new Pais(
            Uuid::toString($row['pais_id']),
            $row['pais_ca'],
            $row['pais_en'],
            new \DateTimeImmutable($row['pais_created_at']),
            new \DateTimeImmutable($row['pais_updated_at'])
        );

        return new Ciutat(
            Uuid::toString($row['id']),
            $row['ciutat'],
            $row['ciutat_ca'],
            $row['ciutat_en'],
            $row['descripcio'],
            $row['ciutat_final'],
            $pais,
            new \DateTimeImmutable($row['created_at']),
            new \DateTimeImmutable($row['updated_at'])
        );
    }
}
