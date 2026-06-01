<?php

use App\Utils\Url;
?>

<div id="barraNavegacioContenidor"></div>
<h1>Gestió Comptabilitat i Clients</h1>
<h2>Llistat de clients</h2>

<div class="d-flex flex-wrap gap-2 my-3">
    <a
        href="<?php echo Url::intranet('comptabilitat'); ?>/nou-client"
        class="btn btn-secondary btn-sm">
        Crear client
    </a>
</div>

<div id="taulaLlistatClients"></div>