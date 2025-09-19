<div class="container">

  <div id="barraNavegacioContenidor"></div>

  <main>
    <div class="container contingut">
      <h1>Gestió Comptabilitat i Clients</h1>
      <div id="isAdminButton" style="display: none;">
        <?php if (isUserAdmin()) { ?>
          <p>
            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['comptabilitat']; ?>/nou-client/'" class="button btn-gran btn-secondari">Afegir client</button>

            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['comptabilitat']; ?>/nou-pressupost/'" class="button btn-gran btn-secondari">Crear pressupost</button>

            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['comptabilitat']; ?>/nova-factura/'" class="button btn-gran btn-secondari">Crear factura clients</button>

            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['comptabilitat']; ?>/facturacio-proveidors/nova-factura/'" class="button btn-gran btn-secondari">Registrar factura proveidor</button>
          </p>

          <div class="alert alert-success quadre">
            <h4 style="text-align: left;">Clients:</h4>
            <ul class="llistat">
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/llistat-clients">Llistat de clients</a></li>
            </ul>

            <h4 style="text-align: left;">Comptabilitat:</h4>
            <ul class="llistat">
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/llistat-series">Llistat de pressupostos</a></li>
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/facturacio-clients">Llistat de factures enviades a clients</a></li>
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/facturacio-proveidors">Llistat de factures rebudes</a></li>
              <li><a href="<?php echo APP_INTRANET . $url['comptabilitat']; ?>/facturacio-anys">Facturació detallada per anys</a></li>
            </ul>
          </div>

        <?php } else {
          // Código que se ejecuta si la condición es falsa (opcional)
        } ?>

      </div>
  </main>
</div>