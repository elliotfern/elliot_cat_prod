<div class="container contingut">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">
            <h1>Taules auxiliars</h1>
            <div id="isAdminButton" style="display: none;">
                <?php if (isUserAdmin()) : ?>
                    <p>
                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['auxiliars']; ?>/nova-imatge/'" class="button btn-gran btn-secondari">Afegir imatge</button>

                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['auxiliars']; ?>/nova-ciutat/'" class="button btn-gran btn-secondari">Afegir ciutat</button>

                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['auxiliars']; ?>/nou-pais/'" class="button btn-gran btn-secondari">Afegir país</button>
                    </p>
                <?php endif; ?>
            </div>

            <div class="alert alert-success quadre">
                <ul class="llistat">
                    <li><a href="<?php echo APP_INTRANET . $url['auxiliars']; ?>/llistat-imatges">Llistat d'imatges</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['auxiliars']; ?>/llistat-ciutats">Llistat de ciutats</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['auxiliars']; ?>/llistat-paisos">Llistat de paisos</a></li>
                </ul>
            </div>

        </div>
    </main>
</div>