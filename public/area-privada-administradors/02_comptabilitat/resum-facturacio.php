<?php

use App\Utils\Routes;
use App\Utils\Button;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Gestió Comptabilitat</h1>

<?php if ($viewModel->isAdmin) : ?>

    <div id="capcaleraComptabilitat"></div>

    <div id="resumComptabilitat"></div>

    <div id="evolucioMensual"></div>

    <div id="graficaEvolucio"></div>

    <div id="resumAnual"></div>

<?php endif; ?>