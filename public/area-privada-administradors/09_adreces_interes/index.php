<div class="container">
    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">
            <h1>Adreces d'interés</h1>
            <?php if (isUserAdmin()) : ?>
                <p>
                    <button onclick="window.location.href='<?php echo APP_INTRANET . $url['adreces']; ?>/nou-link/'" class="button btn-gran btn-secondari">Afegir enllaç</button>
                </p>
            <?php endif; ?>

            <div class="alert alert-success quadre">
                <h4>Tots els enllaços segons:</h4>
                <ul class="llistat">
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/llistat-links">Llistat enllaços</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/llistat-temes">Llistat de temes</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/llistat-subtemes">Llistat de subtemes</a></li>
                </ul>

                <hr>

                <h4>Mitjans de comunicació:</h4>
                <ul class="llistat">
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/tema/88">Mitjans d'economia</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/tema/64">Mitjans progressistes</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/tema/19">Mitjans de Catalunya</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/tema/57">Mitjans de tecnologia</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/tema/9">Ràdios públiques</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/tema/21">Mitjans d'Espanya</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/tema/47">Mitjans d'Irlanda</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/tema/13">Mitjans dels Estats Units</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/tema/20">Mitjans d'Itàlia</a></li>
                </ul>

            </div>
        </div>
    </main>
</div>