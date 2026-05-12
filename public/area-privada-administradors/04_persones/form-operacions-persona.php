    <div id="barraNavegacioContenidor"></div>

    <div class="container form">

      <h2>Base de dades de persones</h2>
      <div id="titolForm"></div>

      <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
      </div>

      <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>
      </div>

      <div class="progress mt-2" style="display:none" id="uploadProgress">
        <div id="uploadProgressBar" class="progress-bar" style="width:0%">0%</div>
      </div>

      <form method="POST" action="" class="row g-3" id="formPersona"
        data-success-redirect-template="/gestio/base-dades-persones/fitxa-persona/{slug}">

        <input type="hidden" name="id" id="id">

        <!-- NOM -->
        <div class="col-md-4">
          <label for="nom" class="form-label">Nom</label>
          <input class="form-control" type="text" name="nom" id="nom">
        </div>

        <!-- COGNOMS -->
        <div class="col-md-4">
          <label for="cognoms" class="form-label">Cognoms</label>
          <input class="form-control" type="text" name="cognoms" id="cognoms">
        </div>

        <!-- SLUG -->
        <div class="col-md-4">
          <label for="slug" class="form-label">Slug</label>
          <input class="form-control" type="text" name="slug" id="slug">
        </div>

        <!-- SEXE -->
        <div class="col-md-4">
          <label for="sexe_id" class="form-label">Gènere</label>
          <select class="form-select" name="sexe_id" id="sexe_id"></select>
        </div>

        <!-- WEB -->
        <div class="col-md-4">
          <label for="web" class="form-label">Pàgina web</label>
          <input class="form-control" type="url" name="web" id="web">
        </div>

        <div class="col-md-4"></div>

        <!-- NAIXEMENT -->
        <div class="col-md-4">
          <label for="dia_naixement" class="form-label">Dia naixement</label>
          <select class="form-select" name="dia_naixement" id="dia_naixement"></select>
        </div>

        <div class="col-md-4">
          <label for="mes_naixement" class="form-label">Mes naixement</label>
          <select class="form-select" name="mes_naixement" id="mes_naixement"></select>
        </div>

        <div class="col-md-4">
          <label for="any_naixement" class="form-label">Any naixement</label>
          <input class="form-control" type="text" name="any_naixement" id="any_naixement">
        </div>

        <!-- DEFUNCIÓ -->
        <div class="col-md-4">
          <label for="dia_defuncio" class="form-label">Dia defunció</label>
          <select class="form-select" name="dia_defuncio" id="dia_defuncio"></select>
        </div>

        <div class="col-md-4">
          <label for="mes_defuncio" class="form-label">Mes defunció</label>
          <select class="form-select" name="mes_defuncio" id="mes_defuncio"></select>
        </div>

        <div class="col-md-4">
          <label for="any_defuncio" class="form-label">Any defunció</label>
          <input class="form-control" type="text" name="any_defuncio" id="any_defuncio">
        </div>

        <!-- CIUTATS -->
        <div class="col-md-4">
          <label for="ciutat_naixement_id" class="form-label">Ciutat naixement</label>
          <select class="form-select" name="ciutat_naixement_id" id="ciutat_naixement_id"></select>
        </div>

        <div class="col-md-4">
          <label for="ciutat_defuncio_id" class="form-label">Ciutat defunció</label>
          <select class="form-select" name="ciutat_defuncio_id" id="ciutat_defuncio_id"></select>
        </div>

        <!-- PAÍS -->
        <div class="col-md-4">
          <label for="pais_autor_id" class="form-label">País</label>
          <select class="form-select" name="pais_autor_id" id="pais_autor_id"></select>
        </div>


        <!-- GRUPS -->
        <div class="col-md-4">
          <label for="grup_ids" class="form-label">Classificació (professió)</label>
          <select class="form-select" name="grup_ids[]" id="grup_ids" multiple required></select>
        </div>

        <hr>
        <!-- IMATGE -->
        <div class="col-md-">
          <label>Imatge coberta existent:</label>
          <select class="form-select" name="img_id" id="img_id"></select>
        </div>

        <div class="col-md-">
          <label>O puja una nova imatge:</label>
          <input class="form-control" type="file" name="img_upload" id="img_upload" accept="image/*">
        </div>

        <div class="col-md-">
          <label>Nom Imatge:</label>
          <input class="form-control" type="text" name="alt" id="alt"></select>
        </div>

        <!-- TRIX -->
        <div class="col-12">
          <label for="descripcio" class="form-label">Descripció</label>

          <input id="descripcio" name="descripcio" type="hidden">
          <trix-editor input="descripcio" class="form-control"></trix-editor>
        </div>

        <!-- BOTONES -->
        <div class="col-12">
          <div class="d-flex justify-content-between mt-3">

            <button type="button" class="btn btn-secondary" onclick="history.back()">
              ← Tornar enrere
            </button>

            <button type="submit" id="btnPersona" class="btn btn-primary">
              Actualitza persona
            </button>

          </div>
        </div>

      </form>
    </div>