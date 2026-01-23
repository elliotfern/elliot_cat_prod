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

  <form method="POST" action="" class="row g-3" id="formPersona" data-success-redirect-template="/gestio/base-dades-persones/fitxa-persona/{slug}">

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
      <select class="form-select" name="sexe_id" id="sexe_id"></select>
    </div>

    <div class="col-md-4">
      <label>Pàgina web:</label>
      <input class="form-control" type="url" name="web" id="web" value="">
    </div>

    <div class="col-md-4"></div>

    <div class="col-md-4">
      <label>Dia de naixement:</label>
      <select class="form-select" name="dia_naixement" id="dia_naixement"></select>
    </div>

    <div class="col-md-4">
      <label>Mes de naixement:</label>
      <select class="form-select" name="mes_naixement" id="mes_naixement"></select>
    </div>

    <div class="col-md-4">
      <label>Any de naixement:</label>
      <input class="form-control" type="text" name="any_naixement" id="any_naixement" value="">
    </div>

    <div class="col-md-4">
      <label>Dia de defunció:</label>
      <select class="form-select" name="dia_defuncio" id="dia_defuncio"></select>
    </div>

    <div class="col-md-4">
      <label>Mes de defunció:</label>
      <select class="form-select" name="mes_defuncio" id="mes_defuncio"></select>
    </div>

    <div class="col-md-4">
      <label>Any de defunció:</label>
      <input class="form-control" type="text" name="any_defuncio" id="any_defuncio" value="">
    </div>

    <div class="col-md-4">
      <label>Ciutat naixement:</label>
      <select class="form-select" name="ciutat_naixement_id" id="ciutat_naixement_id"></select>
    </div>

    <div class="col-md-4">
      <label>Ciutat defunció:</label>
      <select class="form-select" name="ciutat_defuncio_id" id="ciutat_defuncio_id"></select>
    </div>

    <div class="col-md-4">
      <label>País:</label>
      <select class="form-select" name="pais_autor_id" id="pais_autor_id"></select>
    </div>

    <div class="col-md-4">
      <label>Imatge:</label>
      <select class="form-select" name="img_id" id="img_id"></select>
    </div>

    <div class="col-md-4">
      <label for="grups">Classificació grups (professió):</label>
      <select name="grup_ids[]" id="grup_ids" multiple required></select>
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
          <button
            type="button"
            class="btn btn-secondary"
            onclick="history.back()">
            ← Tornar enrere
          </button>
        </div>
        <div class="col-6 text-right derecha">
          <button type="submit" id="btnPersona" class="btn btn-primary">
            Actualitza persona
          </button>
        </div>
      </div>
    </div>

  </form>
</div>