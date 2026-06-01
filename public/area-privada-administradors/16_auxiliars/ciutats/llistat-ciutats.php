<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">

            <h1>Base de dades Ciutats</h1>
            <h2>Llistat complert</h2>

            <div id="isAdminButton" style="display: none;">

                <p>
                    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['auxiliars']; ?>/nova-ciutat/'" class="button btn-gran btn-secondari">Afegir Ciutat</button>
                </p>

            </div>

            <div id="taulaLlistatCiutats"></div>

        </div>
    </main>
</div>