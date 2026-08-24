<?php

use App\Utils\Routes;
use App\Utils\Button;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Gestió Comptabilitat i Clients</h1>
<h3>Llistat de clients</h3>

<?php if ($viewModel->isAdmin) : ?>
    <div class="d-flex flex-wrap gap-2 my-3">
        <?= Button::create('Crear client', Routes::contactes()->nouContacte()) ?>
    </div>

    <div id="taulaLlistatClients"></div>

<?php endif; ?>