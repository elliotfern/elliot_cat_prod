<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">

            <h1>Gestió Comptabilitat i Clients</h1>
            <h2>Llistat de Proveïdors</h2>

            <div id="isAdminButton" style="display: none;">

                <p>
                    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['comptabilitat']; ?>/nou-proveidor/'" class="button btn-gran btn-secondari">Afegir proveidor</button>
                </p>

            </div>
            <div id="taulaLlistatProveidors"></div>
        </div>
    </main>
</div>