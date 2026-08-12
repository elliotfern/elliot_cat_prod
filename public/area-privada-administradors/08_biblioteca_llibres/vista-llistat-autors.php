<?php

use App\Utils\Routes;
use App\Utils\Button;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Biblioteca</h1>
<h2>Llistat d'autors</h2>

<?php if ($viewModel->isAdmin) : ?>
  <div class="d-flex flex-wrap gap-2 my-3">
    <?= Button::create('Crear autor', Routes::persona()->novaPersona())  ?>
  </div>
<?php endif; ?>

<div id="taulaLlistatAutors"></div>