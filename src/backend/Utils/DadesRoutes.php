<?php

namespace App\Utils;

use App\Utils\Url;

class DadesRoutes
{
    private function base(): string
    {
        return Url::intranet('dades');
    }

    // -------------------------
    // CREAR
    // -------------------------
    public function projectes(): string
    {
        return $this->base() . '/nou-medicament';
    }

    public function documents(): string
    {
        return $this->base() . '/nou-facultatiu';
    }

    public function biblioteca(): string
    {
        return $this->base() . '/nova-patologia';
    }

    public function imatges(): string
    {
        return $this->base() . '/llistat-patologies';
    }

    public function musica(): string
    {
        return $this->base() . '/llistat-medicaments';
    }


    public function videos(): string
    {
        return $this->base() . '/llistat-facultatius';
    }

    public function baixades(): string
    {
        return $this->base() . '/dades-doctor-trento';
    }

    public function backups(): string
    {
        return $this->base() . '/dades-doctor-trento';
    }

    public function escriptori(): string
    {
        return $this->base() . '/dades-doctor-trento';
    }
}
