<?php

namespace App\Infrastructure\Persistence\Client;

use App\Domain\Client\Entity\Client;
use App\Domain\Client\ValueObject\ClientId;
use App\Domain\Client\ValueObject\Email;

final class ClientMapper
{
    public static function fromArray(array $row): Client
    {
        return new Client(
            id: new ClientId($row['id']),
            nom: $row['nom'],
            cognoms: $row['cognoms'],
            email: new Email($row['email']),
            web: $row['web'],
            nif: $row['nif'],
            empresa: $row['empresa'],
            adreca: $row['adreca'],
            cp: $row['cp'],
            ciutatId: $row['ciutat_id'],
            provinciaId: $row['provincia_id'],
            paisId: $row['pais_id'],
            telefon: $row['telefon'],
            estatId: $row['estat_id'],
            registre: new \DateTimeImmutable($row['registre'])
        );
    }
}
