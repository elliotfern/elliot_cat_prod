<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container">
            <h1>Adreces d'interés: llistat Sub-temes</h1>
            <?php if (isUserAdmin()) : ?>
                <p>
                    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['adreces']; ?>/nou-subtema/'" class="button btn-gran btn-secondari">Afegir sub-tema</button>
                </p>
            <?php endif; ?>

            <div id="taulaLlistatSubTemes"></div>

        </div>
    </main>
</div>