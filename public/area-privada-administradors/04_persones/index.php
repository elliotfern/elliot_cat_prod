    <div id="barraNavegacioContenidor"></div>

    <div class="container">

        <h1>Base de dades: Persones</h1>

        <?php if (isUserAdmin()) : ?>
            <p>
                <a
                    href="<?php echo APP_INTRANET . $url['persona']; ?>/nova-persona/"
                    class="btn btn-secondary btn-lg">
                    Afegir autor
                </a>
            </p>
        <?php endif; ?>

        <div id="taulaLlistatPersones"></div>
    </div>