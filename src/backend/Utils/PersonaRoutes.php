<?php

namespace App\Utils;

use App\Utils\Url;

class PersonaRoutes
{
    private function base(): string
    {
        return Url::intranet('persona');
    }

    // -------------------------
    // CREAR
    // -------------------------
    public function novaPersona(): string
    {
        return $this->base() . '/nova-persona';
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
    // PAGINES
    // -------------------------

}
