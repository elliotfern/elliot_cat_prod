<?php

use App\Utils\Routes;
use App\Utils\Button;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Gestió Comptabilitat i Clients</h1>
<h3>Llistat de Proveïdors</h3>

<?php if ($viewModel->isAdmin) : ?>
    <div class="d-flex flex-wrap gap-2 my-3">
        <?= Button::create('Crear contacte', Routes::contactes()->nouContacte()) .
            Button::create('Crear proveïdor', Routes::comptabilitat()->nouProveidor()) ?>
    </div>

    <div id="taulaLlistatProveidors"></div>

<?php endif; ?>