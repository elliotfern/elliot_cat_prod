<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
$slug = $routeParams[0];
?>


<div id="barraNavegacioContenidor"></div>


<h1>Viatges</h1>
<h2><span id="titolPagina"></span></h2>

<?php if ($viewModel->isAdmin) : ?>
    <p>
        <button onclick="window.location.href='<?php echo $url['viatges']; ?>/modifica-viatge/<?php echo $slug; ?>'" class="button btn-gran btn-secondari">Modifica viatge</button>

        <button onclick="window.location.href='<?php echo $url['viatges']; ?>/nou-espai'" class="button btn-gran btn-secondari">Afegeix espai</button>
    </p>
<?php endif; ?>

<div class="dadesFitxa" id="dadesFitxa"></div>

<div id="dadesContainer"></div>

<div id="dadesDescripcio"></div>

<div id="taulaLlistatEspaisViatge"></div>

</div>