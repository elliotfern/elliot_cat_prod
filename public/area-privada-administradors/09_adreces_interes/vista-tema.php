<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Adreces d'interés</h1>
<h2><span id="nomTema"></span></h2>

<?php if ($viewModel->isAdmin) : ?>
    <p>
        <button onclick="window.location.href='<?php echo $url['adreces']; ?>/nou-link/'" class="button btn-gran btn-secondari">Afegir enllaç</button>
    </p>
<?php endif; ?>

<div id="taulaLlistatAdreces"></div>

</div>