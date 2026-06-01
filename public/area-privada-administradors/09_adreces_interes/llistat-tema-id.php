<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container">
            <h1>Adreces d'interés: llistat tema</h1>
            <h3><span id="titol"></span></h3>
            <?php if (isUserAdmin()) : ?>
                <p>
                    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['adreces']; ?>/nou-link/'" class="button btn-gran btn-secondari">Afegir enllaç</button>
                </p>
            <?php endif; ?>

            <div id="taulaLlistatTemaId"></div>

        </div>
    </main>
</div>