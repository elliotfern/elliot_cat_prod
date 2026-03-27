<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">

            <h1>Gestió Comptabilitat i Clients</h1>
            <h2>Llistat de productes i serveis</h2>

            <div id="isAdminButton" style="display: none;">

                <p>
                    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['comptabilitat']; ?>/nou-producte/'" class="button btn-gran btn-secondari">Afegir producte</button>
                </p>

            </div>

            <div id="taulaLlistatProductes"></div>

        </div>
    </main>
</div>