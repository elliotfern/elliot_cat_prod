<?php

use App\Utils\Routes;
use App\Utils\Button;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Salut</h1>
<?php if ($viewModel->isAdmin) : ?>
    <div class="d-flex flex-wrap gap-2 my-3">
        <?= Button::create('Crear contacte', Routes::salut()->nouMedicament()) ?>
    </div>

      <div class="alert alert-success quadre">
        <ul class="llistat">
            <li><a href="<?= Routes::salut()->llistatMedicaments() ?>">Llistat de medicaments</a></li>
            <li><a href="<?= Routes::salut()->dadesDoctorTrento() ?>">Dades doctor Trento</a></li>
        </ul>
    </div>

<?php endif; ?>