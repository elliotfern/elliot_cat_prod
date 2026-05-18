<?php

namespace App\Modules\Clients\Schemas;

use App\Utils\Schema\FieldType;

class ClientSchema
{
    public static function create(): array
    {
        return [

            'id' => [
                'type' => FieldType::UUID,
                'required' => true,
                'label' => 'ID',
            ],

            'clientNom' => [
                'type' => FieldType::STRING,
                'required' => false,
                'trim' => true,
                'label' => 'Nom',
            ],

            'clientCognoms' => [
                'type' => FieldType::STRING,
                'required' => false,
                'trim' => true,
                'label' => 'Cognoms',
            ],

            'clientEmail' => [
                'type' => FieldType::STRING,
                'required' => true,
                'trim' => true,
                'label' => 'Email',
            ],

            'clientWeb' => [
                'type' => FieldType::STRING,
                'required' => false,
                'trim' => true,
                'label' => 'Web',
            ],

            'clientNIF' => [
                'type' => FieldType::STRING,
                'required' => false,
                'trim' => true,
                'label' => 'NIF',
            ],

            'clientEmpresa' => [
                'type' => FieldType::STRING,
                'required' => false,
                'trim' => true,
                'label' => 'Empresa',
            ],

            'clientAdreca' => [
                'type' => FieldType::STRING,
                'required' => true,
                'trim' => true,
                'label' => 'Adreça',
            ],

            'clientCP' => [
                'type' => FieldType::STRING,
                'required' => false,
                'trim' => true,
                'label' => 'Codi Postal',
            ],

            'ciutat_id' => [
                'type' => FieldType::UUID,
                'required' => true,
                'label' => 'Ciutat',
            ],

            'provincia_id' => [
                'type' => FieldType::UUID,
                'required' => true,
                'label' => 'Província',
            ],

            'pais_id' => [
                'type' => FieldType::UUID,
                'required' => true,
                'label' => 'País',
            ],

            'clientTelefon' => [
                'type' => FieldType::INT,
                'required' => false,
                'label' => 'Telèfon',
            ],

            'estat_id' => [
                'type' => FieldType::UUID,
                'required' => true,
                'label' => 'Estat',
            ],

            'clientRegistre' => [
                'type' => FieldType::DATE,
                'required' => true,
                'label' => 'Data registre',
            ],
        ];
    }
}
