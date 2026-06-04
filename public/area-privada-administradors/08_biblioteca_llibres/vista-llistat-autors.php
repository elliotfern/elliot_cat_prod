<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Biblioteca</h1>
<h2>Llistat d'autors</h2>

<div id="isAdminButton" style="display: none;">
  <?php if ($viewModel->isAdmin) : ?>
    <p>
      <button onclick="window.location.href='<?php echo $url['persona']; ?>/nova-persona/'" class="button btn-gran btn-secondari">Afegir autor</button>
    </p>
  <?php endif; ?>
</div>

<div id="taulaLlistatAutors"></div>