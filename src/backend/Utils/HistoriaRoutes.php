<?php

namespace App\Utils;

use App\Utils\Url;

class HistoriaRoutes
{
    private function base(): string
    {
        return Url::intranet('historia');
    }

    // -------------------------
    // CREAR
    // -------------------------
    public function nouEsdeveniment(): string
    {
        return $this->base() . '/nou-esdeveniment';
    }

    public function nouCurs(): string
    {
        return $this->base() . '/nou-curs';
    }

    public function nouSlotArticleCurs(): string
    {
        return $this->base() . '/nou-curs-article';
    }

    public function novaPatologia(): string
    {
        return $this->base() . '/nova-patologia';
    }

    // -------------------------
    // PAGINES
    // -------------------------


}
