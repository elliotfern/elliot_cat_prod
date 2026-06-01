<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">
            <h1>Gestió usuaris web</h1>

            <div id="isAdminButton" style="display: none;">
                <?php if (isUserAdmin()) : ?>
                    <p>
                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['usuaris']; ?>/nou-usuari'" class="button btn-gran btn-secondari">Nou usuari</button>
                    </p>
                <?php endif; ?>
            </div>

            <div class="alert alert-success quadre">
                <ul class="llistat">
                    <li> <a href="<?php echo APP_INTRANET . $url['usuaris']; ?>/llistat-usuaris">Llistat d'usuaris</a></li>
                </ul>
            </div>

        </div>
    </main>
</div>