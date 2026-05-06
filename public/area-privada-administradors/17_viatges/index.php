<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">
            <h1>Viatges</h1>

            <div id="isAdminButton" style="display: none;">
                <?php if (isset($_COOKIE['user_id']) && $_COOKIE['user_id'] === '1') : ?>
                    <p>
                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['viatges']; ?>/nou-viatge'" class="button btn-gran btn-secondari">Nou viatge</button>

                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['viatges']; ?>/nou-espai'" class="button btn-gran btn-secondari">Nou espai</button>
                    </p>
                <?php endif; ?>
            </div>

            <div class="alert alert-success quadre">
                <ul class="llistat">
                    <li> <a href="<?php echo APP_INTRANET . $url['viatges']; ?>/llistat-viatges">Llistat de viatges</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['viatges']; ?>/llistat-espais">Llistat d'espais</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['viatges']; ?>/llistat-espais-visitats">Llistat d'espais visitats</a></li>
                </ul>
            </div>

        </div>
    </main>
</div>