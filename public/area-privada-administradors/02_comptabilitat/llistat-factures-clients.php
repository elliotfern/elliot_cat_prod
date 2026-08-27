<?php

use App\Utils\Url;
?>

<div id="barraNavegacioContenidor"></div>
<h1>Gestió Comptabilitat i Clients</h1>
<div id="titolTipusFactura"></div>


<div class="d-flex flex-wrap gap-2 my-3">
    <a
        href="<?php echo Url::intranet('comptabilitat'); ?>/nova-factura"
        class="btn btn-secondary btn-sm">
        Crear factura
    </a>
</div>

<div id="taulaLlistatFactures"></div>