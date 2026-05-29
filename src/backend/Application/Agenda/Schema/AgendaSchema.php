<?php

namespace App\Application\Agenda\Schema;

class AgendaSchema
{
    public static function create(): array
    {
        return [

            'titol' => [
                'rules' => 'required|string|max:255',
                'label' => 'Títol',
            ],

            'descripcio' => [
                'rules' => 'string',
                'label' => 'Descripció',
            ],

            'tipus' => [
                'rules' => 'required|string',
                'label' => 'Tipus',
            ],

            'lloc' => [
                'rules' => 'string',
                'label' => 'Lloc',
            ],

            'ciutat_id' => [
                'rules' => 'uuid',
                'label' => 'Ciutat',
            ],

            'data_inici' => [
                'rules' => 'required|date',
                'label' => 'Data inici',
            ],

            'data_fi' => [
                'rules' => 'date',
                'label' => 'Data fi',
            ],

            'tot_el_dia' => [
                'rules' => 'boolean',
                'label' => 'Tot el dia',
            ],

            'estat' => [
                'rules' => 'required|string',
                'label' => 'Estat',
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

            'titol' => [
                'rules' => 'required|string|max:255',
                'label' => 'Títol',
            ],

            'descripcio' => [
                'rules' => 'string',
                'label' => 'Descripció',
            ],

            'tipus' => [
                'rules' => 'required|string',
                'label' => 'Tipus',
            ],

            'lloc' => [
                'rules' => 'string',
                'label' => 'Lloc',
            ],

            'ciutat_id' => [
                'rules' => 'uuid',
                'label' => 'Ciutat',
            ],

            'data_inici' => [
                'rules' => 'required|date',
                'label' => 'Data inici',
            ],

            'data_fi' => [
                'rules' => 'date',
                'label' => 'Data fi',
            ],

            'tot_el_dia' => [
                'rules' => 'boolean',
                'label' => 'Tot el dia',
            ],

            'estat' => [
                'rules' => 'required|string',
                'label' => 'Estat',
            ],
        ];
    }
}
