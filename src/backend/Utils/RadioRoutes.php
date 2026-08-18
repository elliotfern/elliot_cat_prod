<?php

namespace App\Utils;

use App\Utils\Url;

class RadioRoutes
{
    private function base(): string
    {
        return Url::intranet('radio');
    }

    // -------------------------
    // CREAR
    // -------------------------
    public function rairadiotre(): string
    {
        return $this->base() . '/rai-radio-3';
    }

    public function catmusica(): string
    {
        return $this->base() . '/catalunya-musica';
    }

    public function icatfm(): string
    {
        return $this->base() . '/icatfm';
    }

    public function catinfo(): string
    {
        return $this->base() . '/catalunya-informacio';
    }

    public function bbc4(): string
    {
        return $this->base() . '/bbc-4';
    }

    public function bbc6(): string
    {
        return $this->base() . '/bbc-6';
    }

    public function franceculture(): string
    {
        return $this->base() . '/france-culture';
    }

    public function franceinter(): string
    {
        return $this->base() . '/france-inter';
    }

    public function francemusique(): string
    {
        return $this->base() . '/france-musique';
    }

    public function radiomunicipalterrassa(): string
    {
        return $this->base() . '/radio-municipal-terrassa';
    }


    // -------------------------
    // PAGINES
    // -------------------------

}
