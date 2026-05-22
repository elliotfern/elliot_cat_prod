<?php

namespace App\Application\Client\Presenter;

use App\Domain\Client\Entity\Client;
use App\Utils\Uuid;

final class ClientResponse
{
    public static function toArray(Client $client): array
    {
        return [
            'id' => Uuid::toString($client->id()->value()),
            'nom' => $client->nom(),
            'cognoms' => $client->cognoms(),
            'email' => $client->email()->value(),
            'web' => $client->web(),
            'nif' => $client->nif(),
            'empresa' => $client->empresa(),
            'adreca' => $client->adreca(),
            'cp' => $client->cp(),
            'ciutat_id' => Uuid::toString($client->ciutatId()),
            'provincia_id' => Uuid::toString($client->provinciaId()),
            'pais_id' => Uuid::toString($client->paisId()),
            'telefon' => $client->telefon(),
            'estat_id' => Uuid::toString($client->estatId()),
            'registre' => $client->registre()->format('Y-m-d')
        ];
    }
}
