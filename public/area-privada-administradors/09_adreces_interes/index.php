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
                <h4>Marcadors:</h4>
                <ul class="llistat">
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/llistat-subtema/0199a079-8863-7047-a05a-85e0723e4684">Elliot.cat</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/llistat-subtema/0199a090-d628-70b2-80c7-482f250e18a4">MemòriaTerrassa.cat</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/llistat-subtema/0199a3f0-d3f7-7198-ab0f-6bb987abf73d">Finguer.com</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/llistat-subtema/">Programació web</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/llistat-subtema/">Cinema</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/llistat-subtema/">Música</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/llistat-subtema/">Televisió</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/llistat-subtema/">Altres</a></li>
                </ul>

            </div>
        </div>
    </main>
</div>