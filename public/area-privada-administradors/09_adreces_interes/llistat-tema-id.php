<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Adreces d'interés: llistat tema</h1>
<h3><span id="titol"></span></h3>
<?php if ($viewModel->isAdmin) : ?>
    <p>
        <button onclick="window.location.href='<?php echo $url['adreces']; ?>/nou-link/'" class="button btn-gran btn-secondari">Afegir enllaç</button>
    </p>
<?php endif; ?>

<div id="taulaLlistatTemaId"></div>

</div>