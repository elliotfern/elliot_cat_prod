<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Adreces d'interés: llistat Sub-temes</h1>
<?php if ($viewModel->isAdmin) : ?>
    <p>
        <button onclick="window.location.href='<?php echo $url['auxiliar']; ?>/nou-subtema/'" class="button btn-gran btn-secondari">Afegir sub-tema</button>
    </p>
<?php endif; ?>

<div id="taulaLlistatSubTemes"></div>

</div>