<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">
            <h1>Currículum</h1>

            <div id="isAdminButton" style="display: none;">
                <?php if (isUserAdmin()) : ?>
                    <p>
                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['curriculum']; ?>/nou-perfil'" class="button btn-gran btn-secondari">Nou perfil</button>

                    </p>
                <?php endif; ?>
            </div>

            <div class="alert alert-success quadre">
                <ul class="llistat">
                    <li> <a href="<?php echo APP_INTRANET . $url['curriculum']; ?>/llistat-viatges">Gestió perfil CV</a></li>
                </ul>
            </div>

        </div>
    </main>
</div>