<?php

namespace App\Utils;



class Routes
{
    public static function comptabilitat(): ComptabilitatRoutes
    {
        return new ComptabilitatRoutes();
    }

    public static function biblioteca(): BibliotecaRoutes
    {
        return new BibliotecaRoutes();
    }

    public static function persona(): PersonaRoutes
    {
        return new PersonaRoutes();
    }
}
