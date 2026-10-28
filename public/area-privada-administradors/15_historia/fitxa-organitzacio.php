<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */

?>

<div id="barraNavegacioContenidor"></div>

<h1>Base de dades: Història Oberta</h1>

<?php if ($viewModel->isAdmin) : ?>

    <div id="fitxaOrganitzacio" style="margin-top:25px;margin-bottom:30px"></div>

<?php endif; ?>