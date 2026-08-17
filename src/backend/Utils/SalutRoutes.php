<?php

namespace App\Utils;

use App\Utils\Url;

class SalutRoutes
{
    private function base(): string
    {
        return Url::intranet('salut');
    }

    // -------------------------
    // CREAR
    // -------------------------
    public function nouMedicament(): string
    {
        return $this->base() . '/nou-medicament';
    }

    public function nouFacultatiu(): string
    {
        return $this->base() . '/nou-facultatiu';
    }

    public function novaPatologia(): string
    {
        return $this->base() . '/nova-patologia';
    }

    // -------------------------
    // PAGINES
    // -------------------------

    public function llistatPatologies(): string
    {
        return $this->base() . '/llistat-patologies';
    }

    public function llistatMedicaments(): string
    {
        return $this->base() . '/llistat-medicaments';
    }


    public function llistatFacultatius(): string
    {
        return $this->base() . '/llistat-facultatius';
    }

    public function dadesDoctorTrento(): string
    {
        return $this->base() . '/dades-doctor-trento';
    }
}
