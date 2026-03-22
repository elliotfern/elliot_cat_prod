<div class="container">
  <div id="barraNavegacioContenidor"></div>

  <div class="container contingut">

    <h1>Biblioteca</h1>
    <h2>Llistat de llibres</h2>

    <div id="isAdminButton" style="display: none;">
      <?php if (isUserAdmin()) :  ?>
        <p>
          <button onclick="window.location.href='<?php echo APP_INTRANET . $url['biblioteca']; ?>/nou-llibre/'" class="button btn-gran btn-secondari">Afegir llibre</button>
        </p>
      <?php endif; ?>
    </div>

    <div id="taulaLlistatLlibres"></div>

  </div>
</div>