<?php

use App\Utils\Routes;
use App\Utils\Button;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Biblioteca de llibres</h1>

<?php if ($viewModel->isAdmin) : ?>
    <div class="d-flex flex-wrap gap-2 my-3">
        <?=
        Button::create('Crear llibre', Routes::biblioteca()->nouLlibre()) .
            Button::create('Crear autor', Routes::persona()->novaPersona()) .
            Button::create('Crear col·lecció', Routes::biblioteca()->novaColeccio()) ?>
    </div>

    <div class="alert alert-success quadre">
        <ul class="llistat">
            <li><a href="<?= Routes::biblioteca()->llistatLlibres() ?>">Llistat de llibres</a></li>
            <li><a href="<?= Routes::biblioteca()->llistatAutors() ?>">Llistat d'autors/es</a></li>
            <li><a href="<?= Routes::biblioteca()->llistatColeccions() ?>">Llistat de grups de llibres</a></li>
        </ul>
    </div>
<?php endif; ?>