<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">
            <h1>Viatges</h1>
            <h2>Llistat de viatges</h2>
            <div id="isAdminButton" style="display: none;">
                <?php if (isset($_COOKIE['user_id']) && $_COOKIE['user_id'] === '1') : ?>
                    <p>
                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['viatges']; ?>/nou-viatge'" class="button btn-gran btn-secondari">Nou viatge</button>
                    </p>
                <?php endif; ?>
            </div>

            <div id="taulaLlistatViatges"></div>

        </div>
    </main>
</div>