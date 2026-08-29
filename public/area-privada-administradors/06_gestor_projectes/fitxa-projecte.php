<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */

use App\Utils\Button;
use App\Utils\Routes;

?>

<div id="barraNavegacioContenidor"></div>

<h1>Gestor de projectes</h1>
<h4>Detalls del projecte</h4>

<?php if ($viewModel->isAdmin) : ?>
    <div class="d-flex flex-wrap gap-2 my-3">
        <?= Button::create('Afegir tasca', Routes::projectes()->novaTasca()) ?>
    </div>
<?php endif; ?>

<!-- 1) Header del projecte (títol, badges, etc.) -->
<div id="projecteDetallsHeader" class="mb-4"></div>

<!-- 2) Resum / fitxa del projecte (dades principals) -->
<div id="projecteDetallsFitxa" class="mb-4"></div>

<!-- 3) KPIs / resum de tasques (totals, fetes, bloquejades...) -->
<div id="projecteDetallsKpis" class="mb-4"></div>

<!-- 4) Seccions / pestanyes (TS pot pintar tabs o panels) -->
<div id="projecteDetallsSeccions" class="mb-4"></div>

<!-- 4a) Tasques del projecte -->
<div id="projecteDetallsTasques" class="mb-4"></div>

<!-- 4b) Activitat / logs (opcional) -->
<div id="projecteDetallsActivitat" class="mb-4"></div>

<!-- 4c) Arxius / adjunts (opcional) -->
<div id="projecteDetallsArxius" class="mb-4"></div>