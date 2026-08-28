<?php

namespace App\Utils;

use App\Utils\Url;

class ProjecteRoutes
{
    private function base(): string
    {
        return Url::intranet('projectes');
    }

    // -------------------------
    // CREAR
    // -------------------------
    public function nouProjecte(): string
    {
        return $this->base() . '/nou-projecte';
    }

    public function novaTasca(): string
    {
        return $this->base() . '/nova-tasca';
    }

    // -------------------------
    // PAGINES
    // -------------------------

    public function llistatProjectes(): string
    {
        return $this->base() . '/llistat-projectes';
    }

    public function llistatTasques(): string
    {
        return $this->base() . '/llistat-tasques';
    }
}
