<?php

use App\Utils\Routes;
use App\Utils\Button;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Gestor de projectes</h1>

<?php if ($viewModel->isAdmin) : ?>

    <div class="d-flex flex-wrap gap-2 my-3">
        <?= Button::create('Afegir projecte', Routes::projectes()->nouProjecte()) .
            Button::create('Afegir tasca', Routes::projectes()->novaTasca())
        ?>
    </div>

    <div id="projectesHomePanels" class="mb-4"></div>
    <div id="panelProjectesActius" class="mb-4"></div>
    <div id="taulaProjectes"></div>

    <div class="alert alert-success">
        <h2>Arxiu</h2>
        <br>
        <ul>
            <li><a href="<?php echo Routes::projectes()->llistatProjectes(); ?>">Històric de projectes</a></li>
            <li><a href="<?php echo Routes::projectes()->llistatTasques(); ?>">Històric de tasques</a></li>
        </ul>
    </div>

<?php endif; ?>