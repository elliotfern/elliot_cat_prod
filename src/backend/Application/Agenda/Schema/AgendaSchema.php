<?php

namespace App\Application\Agenda\Schema;

final class AgendaSchema
{
    public static function create(): array
    {
        return [

            'titol' => [
                'type' => 'string',
                'required' => true,
                'min' => 3,
                'max' => 255,
            ],

            'descripcio' => [
                'type' => 'string',
                'required' => false,
                'nullable' => true,
            ],

            'tipus' => [
                'type' => 'string',
                'required' => true,
                'allowed' => [
                    'reunio',
                    'visita_medica',
                    'videotrucada',
                    'viatge',
                    'altre',
                    'aniversari'
                ]
            ],

            'lloc' => [
                'type' => 'string',
                'required' => false,
                'nullable' => true,
            ],

            'ciutat_id' => [
                'type' => 'uuid',
                'required' => false,
                'nullable' => true,
            ],

            'data_inici' => [
                'type' => 'datetime',
                'required' => true,
            ],

            'data_fi' => [
                'type' => 'datetime',
                'required' => false,
                'nullable' => true,
            ],

            'tot_el_dia' => [
                'type' => 'boolean',
                'required' => true,
            ],

            'estat' => [
                'type' => 'string',
                'required' => true,
                'allowed' => [
                    'pendent',
                    'confirmat',
                    'cancel·lat'
                ]
            ],
        ];
    }
}
