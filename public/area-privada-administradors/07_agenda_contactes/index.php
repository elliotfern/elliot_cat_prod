<?php

use App\Utils\Routes;
use App\Utils\Button;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Agenda de contactes</h1>
<?php if ($viewModel->isAdmin) : ?>
  <div class="d-flex flex-wrap gap-2 my-3">
    <?= Button::create('Crear contacte', Routes::contactes()->nouContacte()) ?>
  </div>

  <div id="taulaLlistatContactes"></div>

<?php endif; ?>