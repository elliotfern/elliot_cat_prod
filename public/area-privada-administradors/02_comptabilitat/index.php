<?php

use App\Utils\Routes;
use App\Utils\Button;
?>

<div id="barraNavegacioContenidor"></div>

<h1>Gestió Comptabilitat i Clients</h1>
<?php if (isUserAdmin()) { ?>
  <div class="d-flex flex-wrap gap-2 my-3">
    <?=
    Button::create('Crear client', Routes::comptabilitat()->nouClient()) .
      Button::create('Crear proveïdor', Routes::comptabilitat()->nouProveidor()) .
      Button::create('Crear pressupost', Routes::comptabilitat()->nouPressupost()) .
      Button::create('Crear factura', Routes::comptabilitat()->novaFactura()) .
      Button::create('Crear factura proveïdor', Routes::comptabilitat()->novaFacturaProveidor()) ?>
  </div>

  <div class="alert alert-success">

    <h4>Clients i proveïdors:</h4>
    <ul>
      <li><a href="<?= Routes::comptabilitat()->llistatClients() ?>">Llistat de clients</a></li>
      <li><a href="<?= Routes::comptabilitat()->llistatProveidors() ?>">Llistat de proveïdors</a></li>
    </ul>

    <h4>Comptabilitat (despeses):</h4>
    <ul>
      <li><a href="<?= Routes::comptabilitat()->facturesPartitaIva() ?>">Llistat de factures rebudes (Partita IVA Italia)</a></li>
      <li><a href="<?= Routes::comptabilitat()->facturesAutonomIrlanda() ?>">Llistat de factures rebudes (Autònom Irlanda)</a></li>
      <li><a href="<?= Routes::comptabilitat()->facturesHispantic() ?>">Llistat de factures rebudes (HispanTIC LTD Irlanda)</a></li>
      <li><a href="<?= Routes::comptabilitat()->facturesDespesesPersonals() ?>">Llistat de factures rebudes (despeses personals)</a></li>
    </ul>

    <h4>Comptabilitat (ingressos):</h4>
    <ul>
      <li><a href="<?= Routes::comptabilitat()->facturesClientsPartitaIva() ?>">Llistat de factures enviades a clients (Partita IVA Italia)</a></li>
      <li><a href="<?= Routes::comptabilitat()->facturesClientsAutonomIrlanda() ?>">Llistat de factures enviades a clients (Autònom Irlanda)</a></li>
      <li><a href="<?= Routes::comptabilitat()->facturesClientsHispantic() ?>">Llistat de factures enviades a clients (HispanTIC LTD Irlanda)</a></li>

      <li><a href="<?= Routes::comptabilitat()->facturacioAnys() ?>">Facturació detallada per anys</a></li>
    </ul>

    <h4>Comptabilitat (beneficis detallats):</h4>
    <ul>
      <li><a href="<?= Routes::comptabilitat()->facturacioAnys() ?>">Facturació detallada per anys</a></li>
    </ul>

    <h4>Pressupostos:</h4>
    <ul>
      <li><a href="<?= Routes::comptabilitat()->llistatPressupostos() ?>">Llistat de pressupostos</a></li>
    </ul>

    <h4>Taules auxiliars:</h4>
    <ul>
      <li><a href="<?= Routes::comptabilitat()->llistatCategoriesDespeses() ?>">Llistat de categories de despeses</a></li>
      <li><a href="<?= Routes::comptabilitat()->llistatSubCategoriesDespeses() ?>">Llistat de sub-categories de despeses</a></li>
      <li><a href="<?= Routes::comptabilitat()->llistatEmissors() ?>">Llistat d'emissors de factures</a></li>
      <li><a href="<?= Routes::comptabilitat()->llistatEstatsFacturacio() ?>">Llistat d'estats de facturació</a></li>
      <li><a href="<?= Routes::comptabilitat()->llistatTipusIva() ?>">Llistat de tipus d'IVA</a></li>
      <li><a href="<?= Routes::comptabilitat()->llistatTipusPagament() ?>">Llistat de tipus de pagament</a></li>
      <li><a href="<?= Routes::comptabilitat()->llistatProductes() ?>">Llistat catàleg de productes i serveis</a></li>
    </ul>
  </div>

<?php } else {
  // Código que se ejecuta si la condición es falsa (opcional)
} ?>

</div>