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

    public function nouImatge(): string
    {
        return $this->base() . '/nova-imatge';
    }

    public function novaGaleriaImatges(): string
    {
        return $this->base() . '/nova-galeria-imatges';
    }

    public function novaCiutat(): string
    {
        return $this->base() . '/nova-ciutat';
    }

    public function nouPais(): string
    {
        return $this->base() . '/nou-pais';
    }

    public function nouGrup(): string
    {
        return $this->base() . '/nou-grup';
    }
    // -------------------------
    // PAGINES
    // -------------------------

    public function llistatProfessions(): string
    {
        return $this->base() . '/llistat-grups';
    }

    public function llistatImatges(): string
    {
        return $this->base() . '/llistat-imatges';
    }

    public function llistatGaleriesImatges(): string
    {
        return $this->base() . '/llistat-galeries-imatges';
    }

    public function llistatCiutats(): string
    {
        return $this->base() . '/llistat-ciutats';
    }

    public function llistatPaisos(): string
    {
        return $this->base() . '/llistat-paisos';
    }

    public function llistatTemes(): string
    {
        return $this->base() . '/llistat-temes';
    }

    public function llistatSubTemes(): string
    {
        return $this->base() . '/llistat-subtemes';
    }
}
