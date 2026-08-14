<?php

use App\Utils\Routes;
use App\Utils\Button;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Arts escèniques, cinema i televisió</h1>

<?php if ($viewModel->isAdmin) : ?>
    <div class="d-flex flex-wrap gap-2 my-3">
        <?=
        Button::create('Crear pel·lícula', Routes::cinema()->novaPelicula()) .
            Button::create('Crear sèrie tv', Routes::cinema()->novaSerie()) .
            Button::create('Crear obra teatre', Routes::cinema()->novaObraTeatre()) .
            Button::create('Crear fitxa persona', Routes::persona()->novaPersona()) ?>
    </div>

    <div class="alert alert-success quadre">
        <ul class="llistat">
            <li><a href="<?= Routes::cinema()->llistatPelicules() ?>">Llistat de pel·lícules</a></li>
            <li><a href="<?= Routes::cinema()->llistatSeries() ?>">Llistat de sèries tv</a></li>
            <li><a href="<?= Routes::cinema()->llistatObresTeatre() ?>">Llistat d'obres de teatre</a></li>
            <li><a href="<?= Routes::cinema()->llistatActors() ?>">Llistat d'actors/es</a></li>
            <li><a href="<?= Routes::cinema()->llistatDirectors() ?>">Llistat de directors/es</a></li>
        </ul>
    </div>
<?php endif; ?>