<?php

namespace App\Utils;

use App\Utils\Url;

class CinemaRoutes
{
    private function base(): string
    {
        return Url::intranet('cinema');
    }

    // -------------------------
    // CREAR
    // -------------------------
    public function novaPelicula(): string
    {
        return $this->base() . '/nova-pelicula';
    }

    public function novaSerie(): string
    {
        return $this->base() . '/nova-serie';
    }

    public function novaObraTeatre(): string
    {
        return $this->base() . '/nova-obra-teatre';
    }

    // -------------------------
    // PAGINES
    // -------------------------

    public function llistatPelicules(): string
    {
        return $this->base() . '/llistat-pelicules';
    }

    public function llistatSeries(): string
    {
        return $this->base() . '/llistat-series';
    }

    public function llistatObresTeatre(): string
    {
        return $this->base() . '/llistat-obres-teatre';
    }

    public function llistatActors(): string
    {
        return $this->base() . '/llistat-actors';
    }

    public function llistatDirectors(): string
    {
        return $this->base() . '/llistat-directors';
    }
}
