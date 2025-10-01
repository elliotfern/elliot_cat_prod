<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container">
            <h1>Adreces d'interés: llistat temes</h1>
            <?php if (isUserAdmin()) : ?>
                <p>
                    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['adreces']; ?>/nou-tema/'" class="button btn-gran btn-secondari">Afegir tema</button>
                </p>
            <?php endif; ?>

            <div id="taulaLlistatTemes"></div>

        </div>
    </main>
</div>