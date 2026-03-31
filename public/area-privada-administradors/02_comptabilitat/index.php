<div class="container">

  <div id="barraNavegacioContenidor"></div>

  <main>
    <div class="container contingut">
      <h1>Gestió Comptabilitat i Clients</h1>
      <div id="isAdminButton" style="display: none;">
        <?php if (isUserAdmin()) { ?>
          <p>
            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['comptabilitat']; ?>/nou-client/'" class="button btn-gran btn-secondari">Afegir client</button>

            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['comptabilitat']; ?>/nou-proveidor/'" class="button btn-gran btn-secondari">Afegir proveidor</button>
          </p>

          <p>
            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['comptabilitat']; ?>/nou-pressupost/'" class="button btn-gran btn-secondari">Crear pressupost</button>

            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['comptabilitat']; ?>/nova-factura/'" class="button btn-gran btn-secondari">Crear factura clients</button>

            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['comptabilitat']; ?>/facturacio-proveidors/nova-factura/'" class="button btn-gran btn-secondari">Registrar factura proveidor</button>
          </p>

          <div class="alert alert-success quadre">
            <h4 style="text-align: left;">Clients i proveïdors:</h4>
            <ul class="llistat">
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/llistat-clients">Llistat de clients</a></li>
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/llistat-proveidors">Llistat de proveïdors</a></li>

            </ul>

            <h4 style="text-align: left;">Comptabilitat (despeses):</h4>
            <ul class="llistat">
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/facturacio-proveidors-partita-iva">Llistat de factures rebudes (Partita IVA Italia)</a></li>
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/facturacio-proveidors-autonom-irlanda">Llistat de factures rebudes (Autònom Irlanda)</a></li>
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/facturacio-proveidors-hispantic">Llistat de factures rebudes (HispanTIC LTD Irlanda)</a></li>
            </ul>

            <h4 style="text-align: left;">Comptabilitat (ingressos):</h4>
            <ul class="llistat">
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/facturacio-clients-partita-iva">Llistat de factures enviades a clients (Partita IVA Italia)</a></li>
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/facturacio-clients-autonom-irlanda">Llistat de factures enviades a clients (Autònom Irlanda)</a></li>
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/facturacio-clients-hispantic">Llistat de factures enviades a clients (HispanTIC LTD Irlanda)</a></li>

              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/facturacio-anys">Facturació detallada per anys</a></li>
            </ul>

            <h4 style="text-align: left;">Comptabilitat (beneficis detallats):</h4>
            <ul class="llistat">
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/facturacio-anys">Facturació detallada per anys</a></li>
            </ul>

            <h4 style="text-align: left;">Pressupostos:</h4>
            <ul class="llistat">
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/llistat-series">Llistat de pressupostos</a></li>
            </ul>

            <h4 style="text-align: left;">Taules auxiliars:</h4>
            <ul class="llistat">
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/llistat-series">Llistat de categories de despeses</a></li>
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/llistat-series">Llistat de sub-categories de despeses</a></li>
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/llistat-emissors">Llistat d'emissors de factures</a></li>
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/llistat-series">Llistat d'estats de facturació</a></li>
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/llistat-series">Llistat de tipus d'IVA</a></li>
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/llistat-series">Llistat de tipus de pagament</a></li>
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/llistat-productes">Llistat catàleg de productes i serveis</a></li>
            </ul>

            db_comptabilitat_facturacio_estat
            db_comptabilitat_facturacio_tipus_iva
            db_comptabilitat_facturacio_tipus_pagament
          </div>

        <?php } else {
          // Código que se ejecuta si la condición es falsa (opcional)
        } ?>

      </div>
  </main>
</div>