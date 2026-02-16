<div class="container">

  <div id="barraNavegacioContenidor"></div>

  <main>
    <div class="container contingut">

      <h1>Biblioteca</h1>
      <h2>Llistat d'autors</h2>

      <div id="isAdminButton" style="display: none;">
        <?php if (isUserAdmin()) :  ?>
          <p>
            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['persona']; ?>/nova-persona/'" class="button btn-gran btn-secondari">Afegir autor</button>
          </p>
        <?php endif; ?>
      </div>

      <div id="taulaLlistatAutors"></div>

    </div>
  </main>
</div>