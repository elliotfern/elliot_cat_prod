<?php

namespace App\Modules\Clients\Schema;

class ClientSchema
{
    public static function create(): array
    {
        return [

            'id' => [
                'rules' => 'required|uuid',
                'label' => 'ID',
            ],

            'clientNom' => [
                'rules' => 'string',
                'label' => 'Nom',
            ],

            'clientCognoms' => [
                'rules' => 'string',
                'label' => 'Cognoms',
            ],

            'clientEmail' => [
                'rules' => 'required|string|email|max:255',
                'label' => 'Email',
            ],

            'clientWeb' => [
                'rules' => 'string|max:255',
                'label' => 'Web',
            ],

            'clientNIF' => [
                'rules' => 'string|max:20',
                'label' => 'NIF',
            ],

            'clientEmpresa' => [
                'rules' => 'string|max:255',
                'label' => 'Empresa',
            ],

            'clientAdreca' => [
                'rules' => 'required|string',
                'label' => 'Adreça',
            ],

            'clientCP' => [
                'rules' => 'string|max:10',
                'label' => 'Codi Postal',
            ],

            'ciutat_id' => [
                'rules' => 'required|uuid',
                'label' => 'Ciutat',
            ],

            'provincia_id' => [
                'rules' => 'required|uuid',
                'label' => 'Província',
            ],

            'pais_id' => [
                'rules' => 'required|uuid',
                'label' => 'País',
            ],

            'clientTelefon' => [
                'rules' => 'int',
                'label' => 'Telèfon',
            ],

            'estat_id' => [
                'rules' => 'required|uuid',
                'label' => 'Estat',
            ],

            'clientRegistre' => [
                'rules' => 'required|date',
                'label' => 'Data registre',
            ],
        ];
    }
}
