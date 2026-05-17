<?php

use App\Utils\Url;
?>

<div id="barraNavegacioContenidor"></div>
<h1>Gestió Comptabilitat i Clients</h1>
<h2>Llistat de Proveïdors</h2>

<div class="d-flex flex-wrap gap-2">
    <a
        href="<?php echo Url::intranet('comptabilitat'); ?>/nou-proveidor"
        class="btn btn-secondary btn-sm">
        Crear proveidor
    </a>
</div>

<div id="taulaLlistatProveidors"></div>