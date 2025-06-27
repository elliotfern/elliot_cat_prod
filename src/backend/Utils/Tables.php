<?php

namespace App\Utils;

class Tables
{
    public const PERSONES = 'db_persones';
    public const LLIBRES = 'db_llibres';
    public const LLIBRES_AUTORS = 'db_llibres_autors';
    public const LLIBRES_EDITORIALS = 'db_llibres_editorials';
    public const LLIBRES_TIPUS = 'db_llibres_tipus';
    public const LLIBRES_ESTAT = 'db_llibres_estat';

    public const AUX_TEMES = 'aux_temes';
    public const AUX_SUB_TEMES = 'aux_sub_temes';
    public const AUX_IDIOMES = 'aux_idiomes';


    public const CINEMA_ACTORS_PELICULES = '11_aux_cinema_actors_pelicules';

    // Agrega aquí todas las tablas que necesites usar

    // Método para obtener todas las tablas (opcional)
    public static function all(): array
    {
        return [
            'persones' => self::PERSONES,
            'llibres' => self::LLIBRES,
            'editorials' => self::LLIBRES_EDITORIALS,
            'idiomes' => self::AUX_IDIOMES,
            'estat_llibre' => self::LLIBRES_ESTAT,
            'cinema_actors_pelicules' => self::CINEMA_ACTORS_PELICULES,
        ];
    }
}
