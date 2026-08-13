<?php

use App\Utils\Routes;
use App\Utils\Button;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>
<div id="barraNavegacioContenidor"></div>

<h1>Adreces d'interés: llistat de links</h1>

<?php if ($viewModel->isAdmin) : ?>
    <div class="d-flex flex-wrap gap-2 my-3">
        <?= Button::create('Crear enllaç', Routes::adreces()->nouLink()) ?>
    </div>

    <div id="taulaLlistatLinks"></div>
<?php endif; ?>