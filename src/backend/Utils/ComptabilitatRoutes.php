<?php

namespace App\Utils;

use App\Utils\Url;

class ComptabilitatRoutes
{
    private function base(): string
    {
        return Url::intranet('comptabilitat');
    }

    // -------------------------
    // CREAR
    // -------------------------
    public function nouClient(): string
    {
        return $this->base() . '/nou-client';
    }

    public function nouProveidor(): string
    {
        return $this->base() . '/nou-proveidor';
    }

    public function nouPressupost(): string
    {
        return $this->base() . '/nou-pressupost';
    }

    public function novaFactura(): string
    {
        return $this->base() . '/nova-factura';
    }

    public function novaFacturaProveidor(): string
    {
        return $this->base() . '/nova-factura-proveidor';
    }

    // -------------------------
    // CLIENTS / PROVEÏDORS
    // -------------------------
    public function llistatClients(): string
    {
        return $this->base() . '/llistat-clients';
    }

    public function llistatProveidors(): string
    {
        return $this->base() . '/llistat-proveidors';
    }

    // -------------------------
    // FACTURACIÓ (DESPESES)
    // -------------------------
    public function facturesPartitaIva(): string
    {
        return $this->base() . '/facturacio-proveidors-partita-iva';
    }

    public function facturesAutonomIrlanda(): string
    {
        return $this->base() . '/facturacio-proveidors-autonom-irlanda';
    }

    public function facturesHispantic(): string
    {
        return $this->base() . '/facturacio-proveidors-hispantic';
    }

    public function facturesDespesesPersonals(): string
    {
        return $this->base() . '/facturacio-despeses-personals';
    }

    // -------------------------
    // FACTURACIÓ (INGRESSOS)
    // -------------------------
    public function facturesClientsPartitaIva(): string
    {
        return $this->base() . '/facturacio-clients-partita-iva';
    }

    public function facturesClientsAutonomIrlanda(): string
    {
        return $this->base() . '/facturacio-clients-autonom-irlanda';
    }

    public function facturesClientsHispantic(): string
    {
        return $this->base() . '/facturacio-clients-hispantic';
    }

    public function facturacioAnys(): string
    {
        return $this->base() . '/facturacio-anys';
    }

    // -------------------------
    // PRESSUPOSTOS
    // -------------------------
    public function llistatPressupostos(): string
    {
        return $this->base() . '/llistat-pressupostos';
    }

    // -------------------------
    // TAULES AUXILIARS
    // -------------------------
    public function llistatCategoriesDespeses(): string
    {
        return $this->base() . '/llistat-series';
    }

    public function llistatSubCategoriesDespeses(): string
    {
        return $this->base() . '/llistat-series';
    }

    public function llistatEmissors(): string
    {
        return $this->base() . '/llistat-emissors';
    }

    public function llistatEstatsFacturacio(): string
    {
        return $this->base() . '/llistat-series';
    }

    public function llistatTipusIva(): string
    {
        return $this->base() . '/llistat-series';
    }

    public function llistatTipusPagament(): string
    {
        return $this->base() . '/llistat-series';
    }

    public function llistatProductes(): string
    {
        return $this->base() . '/llistat-productes';
    }
}
