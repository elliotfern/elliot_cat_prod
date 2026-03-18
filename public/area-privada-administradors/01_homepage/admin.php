<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <h1>Intranet</h1>
    <div id="isAdminButton" style="display: none;">
        <?php if (isUserAdmin()) { ?>
            <p>

            </p>

            <div class="alert alert-success quadre">
                <h3>Taulell temes pendents</h3>
                <ul class="llistat">
                    <li><a href="<?php echo APP_INTRANET . $url['taulell_pendents']; ?>/legalitzacio-titol">Legalització títol llicenciatura d'història</a></li>
                </ul>
            </div>

        <?php } else {
            // Código que se ejecuta si la condición es falsa (opcional)
        } ?>

    </div>

</div>