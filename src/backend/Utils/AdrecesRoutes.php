<?php

namespace App\Utils;

use App\Utils\Url;

class AdrecesRoutes
{
    private function base(): string
    {
        return Url::intranet('adreces');
    }

    // -------------------------
    // CREAR
    // -------------------------
    public function nouLink(): string
    {
        return $this->base() . '/nou-link';
    }


    // -------------------------
    // PAGINES
    // -------------------------

    public function llistatLinks(): string
    {
        return $this->base() . '/llistat-links';
    }

    public function llistatTemes(): string
    {
        return $this->base() . '/llistat-temes"';
    }

    public function llistatSubTemes(): string
    {
        return $this->base() . '/llistat-subtemes';
    }

    public function llistatSubTema(): string
    {
        return $this->base() . '/llistat-subtema';
    }

    public function llistatTema(): string
    {
        return $this->base() . '/llistat-tema';
    }
}
