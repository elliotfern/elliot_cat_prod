<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<div class="container">
  <h1>Claus privades</h1>

  <?php if ($viewModel->isAdmin) : ?>

    <div id="taulaLlistatVault"></div>
  <?php endif; ?>
</div>