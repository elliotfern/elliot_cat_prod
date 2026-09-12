<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */

use App\Utils\Button;
use App\Utils\Routes;

?>

<div id="barraNavegacioContenidor"></div>

<h1>Base de dades: Història Oberta</h1>

<?php if ($viewModel->isAdmin) : ?>

    <div class="d-flex flex-wrap gap-2 my-3">
        <?= Button::create('Nou slot article-curs', Routes::historia()->nouSlotArticleCurs()) ?>
    </div>

    <div id="infoCurs"></div>
    <div id="llistatArticles" style="margin-top:25px;margin-bottom:30px"></div>

<?php endif; ?>