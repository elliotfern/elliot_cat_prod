<?php

namespace App\Utils;

use App\Utils\Url;

class BibliotecaRoutes
{
    private function base(): string
    {
        return Url::intranet('biblioteca');
    }

    // -------------------------
    // CREAR
    // -------------------------
    public function nouLlibre(): string
    {
        return $this->base() . '/nou-llibre';
    }

    public function nouAutor(): string
    {
        return $this->base() . '/nou-proveidor';
    }

    public function novaColeccio(): string
    {
        return $this->base() . '/nou-grup';
    }

    // -------------------------
    // PÀGINES
    // -------------------------

    public function llistatLlibres(): string
    {
        return $this->base() . '/llistat-llibres';
    }

    public function llistatAutors(): string
    {
        return $this->base() . '/llistat-autors';
    }

    public function llistatColeccions(): string
    {
        return $this->base() . '/llistat-grups';
    }
}
