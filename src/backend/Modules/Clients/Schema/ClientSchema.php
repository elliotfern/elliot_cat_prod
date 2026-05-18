<?php

namespace App\Modules\Clients\Schema;

use App\Utils\Schema\FieldType;

class ClientSchema
{
    public static function create(): array
    {
        return [

            'id' => [
                'uuid',
                'required',
                'label:ID',
            ],

            'clientNom' => [
                'string',
                'label:Nom',
            ],

            'clientCognoms' => [
                'string',
                'label:Cognoms',
            ],

            'clientEmail' => [
                'string',
                'required',
                'label:Email',
            ],

            'clientWeb' => [
                'string',
                'label:Web',
            ],

            'clientNIF' => [
                'string',
                'label:NIF',
            ],

            'clientEmpresa' => [
                'string',
                'label:Empresa',
            ],

            'clientAdreca' => [
                'string',
                'required',
                'label:Adreça',
            ],

            'clientCP' => [
                'string',
                'label:Codi Postal',
            ],

            'ciutat_id' => [
                'uuid',
                'required',
                'label:Ciutat',
            ],

            'provincia_id' => [
                'uuid',
                'required',
                'label:Província',
            ],

            'pais_id' => [
                'uuid',
                'required',
                'label:País',
            ],

            'clientTelefon' => [
                'int',
                'label:Telèfon',
            ],

            'estat_id' => [
                'uuid',
                'required',
                'label:Estat',
            ],

            'clientRegistre' => [
                'string',
                'required',
                'label:Data registre',
            ],
        ];
    }
}
