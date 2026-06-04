<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Viatges</h1>
<h2>Llistat de viatges</h2>

<?php if ($viewModel->isAdmin) : ?>
    <p>
        <button onclick="window.location.href='<?php echo $url['viatges']; ?>/nou-viatge'" class="button btn-gran btn-secondari">Nou viatge</button>
    </p>
<?php endif; ?>

<div id="taulaLlistatViatges"></div>