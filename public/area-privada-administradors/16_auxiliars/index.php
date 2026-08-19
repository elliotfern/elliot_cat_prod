<?php

use App\Utils\Routes;
use App\Utils\Button;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>


<h1>Taules auxiliars</h1>

<?php if ($viewModel->isAdmin) : ?>
    <div class="d-flex flex-wrap gap-2 my-3">
        <?= Button::create('Nova imatge', Routes::auxiliars()->nouImatge()) .
            Button::create('Nova galeria imatges', Routes::auxiliars()->novaGaleriaImatges()) .
            Button::create('Alta ciutat', Routes::auxiliars()->novaCiutat()) .
            Button::create('Alta país', Routes::auxiliars()->nouPais()) .
            Button::create('Alta professió', Routes::auxiliars()->nouGrup())
        ?>
    </div>

    <div class="alert alert-success quadre">
        <ul class="llistat">
            <li><a href="<?= Routes::auxiliars()->llistatImatges() ?>">Llistat d'imatges</a></li>
            <li><a href="<?= Routes::auxiliars()->llistatGaleriesImatges() ?>">Llistat de galeries d'imatges</a></li>
            <li><a href="<?= Routes::auxiliars()->llistatCiutats() ?>">Llistat de ciutats</a></li>
            <li><a href="<?= Routes::auxiliars()->llistatPaisos() ?>">Llistat de païsps</a></li>
            <li><a href="<?= Routes::auxiliars()->llistatProfessions() ?>">Llistat de grups / professions de persones</a></li>
            <li><a href="<?= Routes::auxiliars()->llistatTemes() ?>">Llistat de temes</a></li>
            <li><a href="<?= Routes::auxiliars()->llistatSubTemes() ?>">Llistat de subtemes</a></li>
        </ul>
    </div>

<?php endif; ?>