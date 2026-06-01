<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">
            <?php if (isUserAdmin()) : ?>
                <h1>Gestió usuaris web</h1>
                <h2>Llistat usuaris</h2>
                <p>
                    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['usuaris']; ?>/nou-usuari'" class="button btn-gran btn-secondari">Nou usuari</button>
                </p>

                <div id="taulaUsuaris"> </div>
        </div>
    <?php endif; ?>
    </main>
</div>