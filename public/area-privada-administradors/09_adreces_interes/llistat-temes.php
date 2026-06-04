<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>


<h1>Adreces d'interés: llistat temes</h1>
<?php if ($viewModel->isAdmin) : ?>
    <p>
        <button onclick="window.location.href='<?php echo $url['auxiliars']; ?>/nou-tema/'" class="button btn-gran btn-secondari">Afegir tema</button>
    </p>
<?php endif; ?>

<div id="taulaLlistatTemes"></div>

</div>