<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>


<div id="barraNavegacioContenidor"></div>

<?php if ($viewModel->isAdmin) : ?>
    <h1>Gestió usuaris web</h1>
    <h2>Llistat usuaris</h2>
    <p>
        <button onclick="window.location.href='<?php echo $url['usuaris']; ?>/nou-usuari'" class="button btn-gran btn-secondari">Nou usuari</button>
    </p>

    <div id="taulaUsuaris"> </div>
    </div>
<?php endif; ?>