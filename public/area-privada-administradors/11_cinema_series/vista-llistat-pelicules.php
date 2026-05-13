  <div id="barraNavegacioContenidor"></div>

  <div class="container">

    <h1>Arts escèniques, cinema i televisió: llistat pel·lícules</h1>

    <div id="isAdminButton" style="display: none;">
      <?php if (isset($_COOKIE['user_id']) && $_COOKIE['user_id'] === '1') : ?>
        <p>
          <button onclick="window.location.href='<?php echo APP_INTRANET . $url['cinema']; ?>/nova-pelicula/'" class="button btn-gran btn-secondari">Afegir pel·lícula</button>

          <button onclick="window.location.href='<?php echo APP_INTRANET . $url['persona']; ?>/nova-persona/'" class="button btn-gran btn-secondari">Afegir actor/a</button>
        </p>
      <?php endif; ?>
    </div>

    <div id="taulaLlistatPelicules"></div>

  </div>