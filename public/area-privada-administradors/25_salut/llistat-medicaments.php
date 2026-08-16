<?php

use App\Utils\Routes;
use App\Utils\Button;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Salut</h1>
<h3>Llistat de medicaments</h3>
<?php if ($viewModel->isAdmin) : ?>
    <div class="d-flex flex-wrap gap-2 my-3">
        <?= Button::create('Crear contacte', Routes::salut()->nouMedicament()) ?>
    </div>


    <div id="taulaLlistatMedicaments"></div>

<?php endif; ?>