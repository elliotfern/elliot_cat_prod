<div id="barraNavegacioContenidor"></div>

<div class="contingut">
    <h1>Gestor de projectes</h1>

    <?php if (isUserAdmin()) : ?>
        <p>
            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['projectes']; ?>/nou-projecte/'"
                class="button btn-gran btn-secondari">
                Afegir projecte
            </button>
        </p>
    <?php endif; ?>

    <div id="projectesHomePanels" class="mb-4"></div>
</div>