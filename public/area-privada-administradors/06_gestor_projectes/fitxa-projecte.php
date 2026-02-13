<?php
$id = $routeParams[0];
?>

<div id="barraNavegacioContenidor"></div>

<div class="contingut">
    <h1>Gestor de projectes</h1>
    <h4 id="subtitolProjecte">Detalls del projecte</h4>

    <?php if (isUserAdmin()) : ?>
        <p>
            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['projectes']; ?>/nova-tasca'"
                class="button btn-gran btn-secondari">
                Afegir tasca
            </button>

            <button onclick="window.location.href='<?php echo APP_INTRANET . $url['projectes']; ?>/modifica-projecte/<?php echo $id; ?>'"
                class="button btn-gran btn-secondari">
                Edita projecte
            </button>
        </p>
    <?php endif; ?>

    <!-- ID per TS -->
    <div id="projecteDetallsMeta" data-project-id="<?php echo (int)$id; ?>"></div>
</div>

<!-- 1) Header del projecte (títol, badges, etc.) -->
<div id="projecteDetallsHeader" class="mb-4" data-project-id="<?php echo (int)$id; ?>"></div>

<!-- 2) Resum / fitxa del projecte (dades principals) -->
<div id="projecteDetallsFitxa" class="mb-4" data-project-id="<?php echo (int)$id; ?>"></div>

<!-- 3) KPIs / resum de tasques (totals, fetes, bloquejades...) -->
<div id="projecteDetallsKpis" class="mb-4" data-project-id="<?php echo (int)$id; ?>"></div>

<!-- 4) Seccions / pestanyes (TS pot pintar tabs o panels) -->
<div id="projecteDetallsSeccions" class="mb-4" data-project-id="<?php echo (int)$id; ?>"></div>

<!-- 4a) Tasques del projecte -->
<div id="projecteDetallsTasques" class="mb-4"></div>

<!-- 4b) Activitat / logs (opcional) -->
<div id="projecteDetallsActivitat" class="mb-4"></div>

<!-- 4c) Arxius / adjunts (opcional) -->
<div id="projecteDetallsArxius" class="mb-4" data-project-id="<?php echo (int)$id; ?>"></div>

</div>