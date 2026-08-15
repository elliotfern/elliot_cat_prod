<?php

namespace App\Utils;

use App\Utils\Url;

class ContacteRoutes
{
    private function base(): string
    {
        return Url::intranet('contactes');
    }

    // -------------------------
    // CREAR
    // -------------------------
    public function nouContacte(): string
    {
        return $this->base() . '/nou-contacte';
    }


    // -------------------------
    // PAGINES
    // -------------------------

}
