<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */

use App\Utils\Button;
use App\Utils\Routes;

?>

<div id="barraNavegacioContenidor"></div>

<h1>Base de dades: Història Oberta</h1>
<h2>Llistat d'organitzacions</h2>

<?php if ($viewModel->isAdmin) : ?>
    <div class="d-flex flex-wrap gap-2 my-3">
        <?=
        Button::create('Crear organitzacio', Routes::historia()->novaOrganitzacio()) ?>
    </div>

    <div id="organitzacions" style="margin-top:25px;margin-bottom:30px"></div>

<?php endif; ?>