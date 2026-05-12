<?php
$slug = $routeParams[0];
?>

<div id="barraNavegacioContenidor"></div>

<div class="container py-4">

  <h1 class="mb-1">Biblioteca de llibres</h1>
  <h3 class="mb-4 text-muted">Fitxa d'informació bàsica del llibre</h3>

  <!-- BOTONES ADMIN -->
  <div id="isAdminButton" class="mb-3" style="display:none;">
    <?php if (isUserAdmin()) : ?>
      <div class="d-flex gap-2 flex-wrap">

        <button
          onclick="window.location.href='<?php echo APP_INTRANET . $url['biblioteca']; ?>/modifica-llibre/<?php echo $slug; ?>'"
          class="btn btn-secondary btn-sm">
          Modifica fitxa
        </button>

      </div>
    <?php endif; ?>
  </div>

  <!-- FECHAS -->
  <div class="alert alert-light border mb-4">
    <strong>Aquesta fitxa ha estat creada el:</strong>
    <span id="dateCreated"></span>
    <span id="dateModified"></span>
  </div>

  <!-- CONTENIDO PRINCIPAL -->
  <div class="row g-4 align-items-start">

    <!-- IMAGEN -->
    <div class="col-12 col-md-4 text-center">
      <img
        id="nameImg"
        src=""
        class="img-fluid img-thumbnail rounded"
        alt="Llibre"
        title="Llibre">
    </div>

    <!-- DATOS -->
    <div class="col-12 col-md-8">

      <div class="card shadow-sm">
        <div class="card-body">

          <div class="quadre-detalls">

            <p><strong>Títol original:</strong> <span id="titol_original"></span></p>
            <p><strong>Títol en català:</strong> <span id="titol_catala"></span></p>

            <div id="linkAutor" class="mb-2"></div>

            <p><strong>Any de publicació:</strong> <span id="any"></span></p>
            <p><strong>Editorial:</strong> <span id="editorial"></span></p>
            <p><strong>Gènere:</strong> <span id="genere_cat"></span></p>
            <p><strong>Sub-gènere:</strong> <span id="sub_genere_cat"></span></p>
            <p><strong>Idioma original:</strong> <span id="idioma_ca"></span></p>
            <p><strong>Tipus d'obra:</strong> <span id="nomTipus"></span></p>

            <div id="linkGrup" class="mb-3"></div>

            <button type="button" class="btn btn-outline-dark btn-sm">
              <span id="estat"></span>
            </button>

          </div>

        </div>
      </div>

    </div>

  </div>
</div>