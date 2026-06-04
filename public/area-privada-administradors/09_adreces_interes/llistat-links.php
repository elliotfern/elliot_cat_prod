<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>
<div id="barraNavegacioContenidor"></div>


<div class="container">
    <h1>Adreces d'interés: llistat de links</h1>
    <?php if ($viewModel->isAdmin) : ?>
        <p>
            <button onclick="window.location.href='<?php echo $url['adreces']; ?>/nou-link/'" class="button btn-gran btn-secondari">Afegir enllaç</button>
        </p>
    <?php endif; ?>

    <div id="taulaLlistatLinks"></div>

</div>