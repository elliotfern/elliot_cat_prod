<div class="barraNavegacio">
</div>

<div class="container-fluid form">

  <h2>Base de dades de persones</h2>
  <div id="titolForm"></div>

  <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
    <div id="okText"></div>
  </div>

  <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
    <div id="errText"></div>

  </div>

  <form method="POST" action="" class="row g-3" id="formPersona" data-success-redirect-template="<?php echo APP_URL; ?>/gestio/base-dades-persones/fitxa-persona/{slug}">

    <input type="hidden" name="id" id="id" value="">

    <div class="col-md-4">
      <label>Nom:</label>
      <input class="form-control" type="text" name="nom" id="nom" value="">
    </div>

    <div class="col-md-4">
      <label>Cognoms:</label>
      <input class="form-control" type="text" name="cognoms" id="cognoms" value="">
    </div>

    <div class="col-md-4">
      <label>Slug:</label>
      <input class="form-control" type="text" name="slug" id="slug" value="">
    </div>

    <div class="col-md-4">
      <label>Gènere:</label>
      <select class="form-select" name="sexeId" id="sexeId"></select>
    </div>

    <div class="col-md-4">
      <label>Pàgina web:</label>
      <input class="form-control" type="url" name="web" id="web" value="">
    </div>

    <div class="col-md-4"></div>

    <div class="col-md-4">
      <label>Dia de naixement:</label>
      <select class="form-select" name="diaNaixement" id="diaNaixement"></select>
    </div>

    <div class="col-md-4">
      <label>Mes de naixement:</label>
      <select class="form-select" name="mesNaixement" id="mesNaixement"></select>
    </div>

    <div class="col-md-4">
      <label>Any de naixement:</label>
      <input class="form-control" type="text" name="anyNaixement" id="anyNaixement" value="">
    </div>

    <div class="col-md-4">
      <label>Dia de defunció:</label>
      <select class="form-select" name="diaDefuncio" id="diaDefuncio"></select>
    </div>

    <div class="col-md-4">
      <label>Mes de defunció:</label>
      <select class="form-select" name="mesDefuncio" id="mesDefuncio"></select>
    </div>

    <div class="col-md-4">
      <label>Any de defunció:</label>
      <input class="form-control" type="text" name="anyDefuncio" id="anyDefuncio" value="">
    </div>

    <div class="col-md-4">
      <label>Ciutat naixement:</label>
      <select class="form-select" name="ciutatNaixementId" id="ciutatNaixementId"></select>
    </div>

    <div class="col-md-4">
      <label>Ciutat defunció:</label>
      <select class="form-select" name="ciutatDefuncioId" id="ciutatDefuncioId"></select>
    </div>

    <div class="col-md-4">
      <label>País:</label>
      <select class="form-select" name="paisAutorId" id="paisAutorId"></select>
    </div>

    <div class="col-md-4">
      <label>Imatge:</label>
      <select class="form-select" name="imgId" id="imgId"></select>
    </div>

    <div class="col-md-4">
      <label for="grups">Classificació grups (professió):</label>
      <select name="grups[]" id="grups" multiple required></select>
    </div>

    <div class="col-md-4"></div>
    <div class="col-md-4"></div>

    <div class="col-complet">
      <label for="descripcio" class="form-label">Descripció:</label>
      <input id="descripcio" name="descripcio" type="hidden">
      <trix-editor input="descripcio" class="trix-editor"></trix-editor>
    </div>

    <div class="container">
      <div class="row">
        <div class="col-6 text-left">

        </div>
        <div class="col-6 text-right derecha">

          <button type="submit" id="btnPersona" class="btn btn-primary">Actualitza persona</button>

        </div>
      </div>
    </div>


  </form>
</div>