<?php

use App\Utils\Routes;
use App\Utils\Button;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Arts escèniques, cinema i televisió</h1>
<h3>Llistat de sèries tv</h3>

<?php if ($viewModel->isAdmin) : ?>
  <div class="d-flex flex-wrap gap-2 my-3">
    <?=
    Button::create('Crear sèrie tv', Routes::cinema()->novaSerie()) .
      Button::create('Crear fitxa persona', Routes::persona()->novaPersona()) ?>
  </div>

  <div id="taulaLlistatSeries"></div>

<?php endif; ?>