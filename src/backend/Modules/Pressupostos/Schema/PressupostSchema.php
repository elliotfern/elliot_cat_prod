<?php

namespace App\Modules\Pressupostos\Schema;

class PressupostSchema
{
    public static function create(): array
    {
        return [

            'concepte' => [
                'rules' => 'required|string|max:255',
                'label' => 'Concepte',
            ],

            'client_id' => [
                'rules' => 'required|uuid',
                'label' => 'Client',
            ],

            'servei_id' => [
                'rules' => 'required|uuid',
                'label' => 'Servei',
            ],

            'estat_id' => [
                'rules' => 'required|uuid',
                'label' => 'Estat',
            ],

            'import' => [
                'rules' => 'required|numeric',
                'label' => 'Import',
            ],

            'data' => [
                'rules' => 'required|date',
                'label' => 'Data',
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

            'concepte' => [
                'rules' => 'required|string|max:255',
                'label' => 'Concepte',
            ],

            'client_id' => [
                'rules' => 'required|uuid',
                'label' => 'Client',
            ],

            'servei_id' => [
                'rules' => 'required|uuid',
                'label' => 'Servei',
            ],

            'estat_id' => [
                'rules' => 'required|uuid',
                'label' => 'Estat',
            ],

            'import' => [
                'rules' => 'required|numeric',
                'label' => 'Import',
            ],

            'data' => [
                'rules' => 'required|date',
                'label' => 'Data',
            ],
        ];
    }
}
