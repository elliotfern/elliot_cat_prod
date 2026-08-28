<?php

use App\Utils\Routes;
use App\Utils\Button;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Gestor de projectes</h1>
<h3>Històric llistat de projectes</h3>

<?php if ($viewModel->isAdmin) : ?>

    <div class="d-flex flex-wrap gap-2 my-3">
        <?= Button::create('Afegir projecte', Routes::projectes()->nouProjecte()) ?>
    </div>

    <div id="taulaLlistatProjectes"></div>

<?php endif; ?>