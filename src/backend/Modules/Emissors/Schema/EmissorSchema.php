<?php

namespace App\Modules\Emissors\Schema;

class EmissorSchema
{
    public static function create(): array
    {
        return [

            'nom' => [
                'rules' => 'required|string|max:255',
                'label' => 'Nom i cognoms',
            ],

            'nif' => [
                'rules' => 'required|string|max:50',
                'label' => 'NIF',
            ],

            'numero_iva' => [
                'rules' => 'nullable|string|max:50',
                'label' => 'Número IVA',
            ],

            'pais_id' => [
                'rules' => 'required|uuid',
                'label' => 'País',
            ],

            'adreca' => [
                'rules' => 'nullable|string|max:255',
                'label' => 'Adreça',
            ],

            'telefon' => [
                'rules' => 'nullable|string|max:30',
                'label' => 'Telèfon',
            ],

            'email' => [
                'rules' => 'nullable|email|max:255',
                'label' => 'Email',
            ],
        ];
    }

    public static function update(): array
    {
        return [

            'id' => [
                'rules' => 'required|uuid',
                'label' => 'ID',
            ],

            'nom' => [
                'rules' => 'required|string|max:255',
                'label' => 'Nom i cognoms',
            ],

            'nif' => [
                'rules' => 'required|string|max:50',
                'label' => 'NIF',
            ],

            'numero_iva' => [
                'rules' => 'nullable|string|max:50',
                'label' => 'Número IVA',
            ],

            'pais_id' => [
                'rules' => 'required|uuid',
                'label' => 'País',
            ],

            'adreca' => [
                'rules' => 'nullable|string|max:255',
                'label' => 'Adreça',
            ],

            'telefon' => [
                'rules' => 'nullable|string|max:30',
                'label' => 'Telèfon',
            ],

            'email' => [
                'rules' => 'nullable|email|max:255',
                'label' => 'Email',
            ],
        ];
    }
}
