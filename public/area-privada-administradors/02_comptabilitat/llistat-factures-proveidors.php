<?php

use App\Utils\Url;
?>

<div id="barraNavegacioContenidor"></div>
<h1>Gestió Comptabilitat i Clients</h1>
<h2>LListat factures rebudes - <div id="titolTipusFactura"></div>
</h2>

<div class="d-flex flex-wrap gap-2 my-3">
    <a
        href="<?php echo Url::intranet('comptabilitat'); ?>/nova-factura-proveidor"
        class="btn btn-secondary btn-sm">
        Crear factura proveidor
    </a>
</div>

<div id="taulaLlistatFacturesProveidors"></div>