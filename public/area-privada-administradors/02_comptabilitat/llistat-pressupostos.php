<?php

use App\Utils\Url;
?>

<div id="barraNavegacioContenidor"></div>
<h1>Gestió Comptabilitat i Clients</h1>
<h2>Llistat de pressupostos elaborats</h2>

<div class="d-flex flex-wrap gap-2 my-3">
    <a
        href="<?php echo Url::intranet('comptabilitat'); ?>/nou-pressupost"
        class="btn btn-secondary btn-sm">
        Crear pressupost
    </a>
</div>

<div id="taulaLlistatPressupostos"></div>