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

    public static function adreces(): AdrecesRoutes
    {
        return new AdrecesRoutes();
    }

    public static function auxiliars(): AuxiliarsRoutes
    {
        return new AuxiliarsRoutes();
    }

    public static function cinema(): CinemaRoutes
    {
        return new CinemaRoutes();
    }

    public static function vault(): VaultRoutes
    {
        return new VaultRoutes();
    }

    public static function contactes(): ContacteRoutes
    {
        return new ContacteRoutes();
    }

    public static function salut(): SalutRoutes
    {
        return new SalutRoutes();
    }

    public static function radio(): RadioRoutes
    {
        return new RadioRoutes();
    }
}
