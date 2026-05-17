<?php

use App\Utils\Url;
?>

<div id="barraNavegacioContenidor"></div>

<h1>Gestió Comptabilitat i Clients</h1>
<?php if (isUserAdmin()) { ?>
  <div class="d-flex flex-wrap gap-2">
    <a
      href="<?php echo Url::intranet('comptabilitat'); ?>/nou-client"
      class="btn btn-secondary btn-sm">
      Crear client
    </a>

    <a
      href="<?php echo Url::intranet('comptabilitat'); ?>/nou-proveidor"
      class="btn btn-secondary btn-sm">
      Crear proveïdor
    </a>

    <a
      href="<?php echo Url::intranet('comptabilitat'); ?>/nou-pressupost"
      class="btn btn-secondary btn-sm">
      Crear pressupost
    </a>

    <a
      href="<?php echo Url::intranet('comptabilitat'); ?>/nou-factura"
      class="btn btn-secondary btn-sm">
      Crear factura
    </a>

    <a
      href="<?php echo Url::intranet('comptabilitat'); ?>/nova-factura-proveidor"
      class="btn btn-secondary btn-sm">
      Crear factura proveidor
    </a>
  </div>

  <div class="alert alert-success">
    <h4>Clients i proveïdors:</h4>
    <ul>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-clients">Llistat de clients</a></li>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-proveidors">Llistat de proveïdors</a></li>
    </ul>

    <h4>Comptabilitat (despeses):</h4>
    <ul>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/facturacio-proveidors-partita-iva">Llistat de factures rebudes (Partita IVA Italia)</a></li>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/facturacio-proveidors-autonom-irlanda">Llistat de factures rebudes (Autònom Irlanda)</a></li>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/facturacio-proveidors-hispantic">Llistat de factures rebudes (HispanTIC LTD Irlanda)</a></li>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/facturacio-despeses-personals">Llistat de factures rebudes (despeses personals)</a></li>
    </ul>

    <h4>Comptabilitat (ingressos):</h4>
    <ul>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/facturacio-clients-partita-iva">Llistat de factures enviades a clients (Partita IVA Italia)</a></li>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/facturacio-clients-autonom-irlanda">Llistat de factures enviades a clients (Autònom Irlanda)</a></li>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/facturacio-clients-hispantic">Llistat de factures enviades a clients (HispanTIC LTD Irlanda)</a></li>

      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/facturacio-anys">Facturació detallada per anys</a></li>
    </ul>

    <h4>Comptabilitat (beneficis detallats):</h4>
    <ul>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/facturacio-anys">Facturació detallada per anys</a></li>
    </ul>

    <h4>Pressupostos:</h4>
    <ul>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-pressupostos">Llistat de pressupostos</a></li>
    </ul>

    <h4>Taules auxiliars:</h4>
    <ul>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-series">Llistat de categories de despeses</a></li>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-series">Llistat de sub-categories de despeses</a></li>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-emissors">Llistat d'emissors de factures</a></li>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-series">Llistat d'estats de facturació</a></li>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-series">Llistat de tipus d'IVA</a></li>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-series">Llistat de tipus de pagament</a></li>
      <li><a href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-productes">Llistat catàleg de productes i serveis</a></li>
    </ul>


  <?php } else {
  // Código que se ejecuta si la condición es falsa (opcional)
} ?>

  </div>