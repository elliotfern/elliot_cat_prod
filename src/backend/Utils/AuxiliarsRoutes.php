<?php

namespace App\Utils;

use App\Utils\Url;

class AuxiliarsRoutes
{
    private function base(): string
    {
        return Url::intranet('auxiliars');
    }

    // -------------------------
    // CREAR
    // -------------------------
    public function nouTema(): string
    {
        return $this->base() . '/nou-tema';
    }

    public function nouSubTema(): string
    {
        return $this->base() . '/nou-subtema';
    }


    // -------------------------
    // PAGINES
    // -------------------------

}
