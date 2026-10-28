<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */

?>

<div id="barraNavegacioContenidor"></div>

<h1>Base de dades: Història Oberta</h1>
<h4>Fitxa esdeveniment</h4>

<?php if ($viewModel->isAdmin) : ?>

    <div id="fitxaEsdeveniment" style="margin-top:25px;margin-bottom:30px"></div>

    <hr>

    <div class="quadre-extra"></div>
<?php endif; ?>