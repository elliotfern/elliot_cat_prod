<?php

use App\Utils\Url;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;
$urlModifica = Url::intranet('persones') . '/modifica-persona/' . urlencode($slug);
?>

<div id="barraNavegacioContenidor"></div>

<h1>Base de dades Persones</h1>

<div id="isAdminButton" style="display: none;">
    <?php if (isUserAdmin()) : ?>
        <p>
            <a
                href="<?php echo $urlModifica; ?>/nova-persona/"
                class="btn btn-secondary btn-sm">
                Modifica fitxa
            </a>
        </p>
    <?php endif; ?>
</div>

<div class="dadesFitxa bg-light p-3 rounded w-auto d-inline-block mb-3" style="background-color: #D4D4D4;padding:15px; border-radius:10px;width:fit-content;">
    <strong>Aquesta fitxa ha estat creada el: </strong><span id="dateCreated"></span> <span id="dateModified"></span>
</div>

<!-- 🔥 FILA PRINCIPAL -->
<div class="row">

    <!-- 🟦 TÍTOL OCUPA TOTA LA FILA -->
    <div class="col-12">
        <h2 class="mb-4" id="nom"></h2>
    </div>

    <!-- 🖼️ IMAGEN -->
    <div class="col-12 col-md-4 text-center mb-3 mb-md-0">
        <img id="nameImg" src="" class="img-fluid img-thumbnail" alt="Imatge" title="Imatge">
        <p class="mt-2"><span id="alt"></span></p>
    </div>

    <!-- 📄 DETALLES -->
    <div class="col-12 col-md-8">
        <div class="quadre-detalls"></div>
    </div>

</div>

<hr>

<div class="quadre-professio"></div>