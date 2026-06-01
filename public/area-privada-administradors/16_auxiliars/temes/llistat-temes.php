<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">

            <h1>Base de dades Temes i subtemes</h1>
            <h2>Llistat de temes</h2>

            <div id="isAdminButton" style="display: none;">

                <p>
                    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['auxiliars']; ?>/nou-tema/'" class="button btn-gran btn-secondari">Afegir tema</button>
                </p>

            </div>

            <div id="taulaLlistatTemes"></div>

        </div>
    </main>
</div>