<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">

            <h1>Base de dades Persones</h1>
            <h2>Llistat complert de grups/professions</h2>

            <div id="isAdminButton" style="display: none;">

                <p>
                    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['auxiliars']; ?>/nou-grup/'" class="button btn-gran btn-secondari">Afegir grup</button>
                </p>

            </div>

            <div id="taulaLlistatGrupsPersones"></div>

        </div>
    </main>
</div>