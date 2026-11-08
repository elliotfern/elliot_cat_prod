<?php

use App\Utils\Routes;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Gestor correu electrònic</h1>
<?php if ($viewModel->isAdmin) : ?>

    <!-- correus -->
    <div id="gestorCorreu"></div>

<?php endif; ?>