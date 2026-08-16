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

    // -------------------------
    // PAGINES
    // -------------------------

    public function llistatMedicaments(): string
    {
        return $this->base() . '/llistat-medicaments';
    }

    public function dadesDoctorTrento(): string
    {
        return $this->base() . '/dades-doctor-trento';
    }
}
