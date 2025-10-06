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
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/llistat-tema/0197acfe-0628-70e4-a2d3-d75ede595dd8/">Programació web</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/llistat-tema/0197acfe-0628-70e4-a2d3-d75edf565f0c">Cinema i tv</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/llistat-subtema/0199ae6f-24c9-7395-9bfc-87cc72c50939">Playlist Música</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['adreces']; ?>/llistat-subtema/">Altres</a></li>
                </ul>

            </div>
        </div>
    </main>
</div>