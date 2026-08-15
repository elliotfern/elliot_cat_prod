<?php

namespace App\Utils;

use App\Utils\Url;

class VaultRoutes
{
    private function base(): string
    {
        return Url::intranet('vault');
    }

    // -------------------------
    // CREAR
    // -------------------------
    public function novaClau(): string
    {
        return $this->base() . '/nou-vault';
    }

    // -------------------------
    // PAGINES
    // -------------------------

}
