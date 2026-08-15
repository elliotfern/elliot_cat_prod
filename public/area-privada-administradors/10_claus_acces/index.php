<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */

use App\Utils\Button;
use App\Utils\Routes;

?>

<div id="barraNavegacioContenidor"></div>

<h1>Claus privades</h1>

<?php if ($viewModel->isAdmin) : ?>
  <div class="d-flex flex-wrap gap-2 my-3">
    <?= Button::create('Crear clau', Routes::vault()->novaClau())  ?>
  </div>

  <div id="taulaLlistatVault"></div>
<?php endif; ?>