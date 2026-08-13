<?php

use App\Utils\Routes;
use App\Utils\Button;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Adreces d'interés</h1>
<h2><span id="nomTema"></span></h2>

<?php if ($viewModel->isAdmin) : ?>
    <div class="d-flex flex-wrap gap-2 my-3">
        <?= Button::create('Crear enllaç', Routes::adreces()->nouLink()) ?>
    </div>

    <div id="taulaLlistatAdreces"></div>

<?php endif; ?>