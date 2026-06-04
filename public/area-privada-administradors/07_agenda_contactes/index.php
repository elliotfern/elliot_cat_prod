<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<div class="container contingut">
  <h1>Agenda de contactes</h1>

  <?php if ($viewModel->isAdmin) : ?>
    <p>
      <button onclick="window.location.href='<?php echo $url['contactes']; ?>/nou-contacte/'" class="button btn-gran btn-secondari">Afegir contacte</button>
    </p>
  <?php endif; ?>

  <div id="taulaLlistatContactes"></div>

</div>