<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">
            <h1>Agenda esdeveniments</h1>

            <div id="isAdminButton" style="display: none;">
                <?php if (isUserAdmin()) : ?>
                    <p>
                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['agenda']; ?>/nou-esdeveniment'" class="button btn-gran btn-secondari">Nou esdeveniment</button>

                    </p>
                <?php endif; ?>
            </div>

            <div class="alert alert-success quadre">
                <ul class="llistat">
                    <li><a href="<?php echo APP_INTRANET . $url['agenda']; ?>/llistat-esdeveniments">Veure llistat propers esdeveniments</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['agenda']; ?>/calendari-esdeveniments">Veure calendari</a></li>
                </ul>
            </div>

        </div>
    </main>
</div>