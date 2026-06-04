<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<div class="contingut">
    <h1>Gestor de projectes</h1>

    <?php if ($viewModel->isAdmin) : ?>

        <p>
            <button onclick="window.location.href='<?php echo $url['projectes']; ?>/nou-projecte/'"
                class="button btn-gran btn-secondari">
                Afegir projecte
            </button>

            <button onclick="window.location.href='<?php echo $url['projectes']; ?>/nova-tasca/'"
                class="button btn-gran btn-secondari">
                Afegir tasca
            </button>
        </p>
    <?php endif; ?>

    <div id="projectesHomePanels" class="mb-4"></div>
    <div id="panelProjectesActius" class="mb-4"></div>
    <div id="taulaProjectes"></div>
</div>