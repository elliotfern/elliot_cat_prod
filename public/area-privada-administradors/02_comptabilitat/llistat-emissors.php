<?php

use App\Utils\Url;
?>

<div id="barraNavegacioContenidor"></div>
<h1>Gestió Comptabilitat i Clients</h1>
<h2>Llistat d'emissors de factures</h2>

<div class="d-flex flex-wrap gap-2">
    <a
        href="<?php echo Url::intranet('comptabilitat'); ?>/nou-emissor"
        class="btn btn-secondary btn-sm">
        Crear emissor
    </a>
</div>

<div id="taulaLlistatEmissors"></div>