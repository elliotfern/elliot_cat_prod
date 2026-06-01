<?php

namespace App\Utils;

class AppRoutes
{
    /**
     * URLs públicas de la intranet (listas para usar en links)
     */

    public const INTRANET_BASE = '/gestio';

    public const ROUTES = [

        // 🔹 Módulos principales
        'homepage'      => '/',
        'comptabilitat' => '/comptabilitat',
        'persones'      => '/base-dades-persones',
        'programacio'   => '/programacio',
        'projectes'     => '/projectes',
        'contactes'     => '/agenda-contactes',
        'biblioteca'    => '/biblioteca',
        'adreces'       => '/adreces',
        'vault'         => '/claus-privades',
        'cinema'        => '/cinema',
        'xarxes'        => '/xarxes-socials',
        'blog'          => '/blog',
        'rss'           => '/lector-rss',
        'historia'      => '/historia',
        'auxiliars'     => '/auxiliars',
        'viatges'       => '/viatges',
        'usuaris'       => '/usuaris',
        'radio'         => '/radio',
        'curriculum'    => '/curriculum',
        'agenda'        => '/agenda',
        'taulell-pendents' => '/taulell-pendents',

        // 🔹 Casos específicos
        'taulell_legalitzacio' => '/taulell-pendents/legalitzacio-titol',
    ];

    public static function get(string $key): ?string
    {
        return self::ROUTES[$key] ?? null;
    }


    public static function intranet(string $key): ?string
    {
        $path = self::get($key);

        return $path ? self::INTRANET_BASE . $path : null;
    }
}
