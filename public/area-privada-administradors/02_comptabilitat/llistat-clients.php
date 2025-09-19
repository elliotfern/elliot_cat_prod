<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">

            <h1>Gestió Comptabilitat i Clients</h1>
            <h2>Llistat de clients</h2>

            <div id="isAdminButton" style="display: none;">

                <p>
                    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['comptabilitat']; ?>/nou-client/'" class="button btn-gran btn-secondari">Afegir client</button>
                </p>

            </div>

            <div id="taulaLlistatClients"></div>

        </div>
    </main>
</div>