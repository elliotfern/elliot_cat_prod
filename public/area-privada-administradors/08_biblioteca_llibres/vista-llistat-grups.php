<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <div class="contingut">

        <h1>Biblioteca</h1>
        <h2>Llistat de grups de llibres</h2>

        <div id="isAdminButton" style="display: none;">
            <?php if (isUserAdmin()) :  ?>
                <p>
                    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['biblioteca']; ?>/nou-grup/'" class="button btn-gran btn-secondari">Afegir grup</button>
                </p>
            <?php endif; ?>
        </div>

        <div id="taulaLlistatGrups"></div>

    </div>
</div>