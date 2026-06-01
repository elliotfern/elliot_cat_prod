<?php

use App\Utils\Url;
?>

<div id="barraNavegacioContenidor"></div>
<h1>Gestió Comptabilitat i Clients</h1>
<h2>Llistat de productes i serveis</h2>

<div class="d-flex flex-wrap gap-2 my-3">
    <a
        href="<?php echo Url::intranet('comptabilitat'); ?>/nou-producte"
        class="btn btn-secondary btn-sm">
        Crear producte
    </a>
</div>

<div id="taulaLlistatProductes"></div>