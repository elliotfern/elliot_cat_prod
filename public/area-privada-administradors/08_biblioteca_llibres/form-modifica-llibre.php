<div id="barraNavegacioContenidor"></div>

<div class="form">
  <h2>Base de dades: Biblioteca</h2>
  <h4 id="titolForm"></h4>

  <div id="okMessage" class="alert alert-success d-none">
    <span id="okText"></span>
  </div>

  <div id="errMessage" class="alert alert-danger d-none">
    <span id="errText"></span>
  </div>

  <div class="progress mt-2 d-none" id="uploadProgress">
    <div id="uploadProgressBar" class="progress-bar" style="width:0%">0%</div>
  </div>

  <form id="formLlibre" class="row g-3">

    <input type="hidden" id="id" name="id" value="">

    <div class="col-md-4">
      <label>Títol llibre en llengua original:</label>
      <input class="form-control" type="text" name="titol_original" id="titol_original" value="">
    </div>

    <div class="col-md-4">
      <label>Títol llibre en llengua catalana:</label>
      <input class="form-control" type="text" name="titol_catala" id="titol_catala" value="">
    </div>

    <div class="col-md-4">
      <label>Slug:</label>
      <input class="form-control" type="text" name="slug" id="slug" value="">
    </div>

    <div class="col-md-4">
      <label>Any de publicació:</label>
      <input class="form-control" type="text" name="any" id="any" value="">
    </div>

    <div class="col-md-4">
      <label> Editorial:</label>
      <select class="form-select" name="editorial_id" id="editorial_id"></select>
      </select>
    </div>

    <div class="col-md-4">
      <label>Idioma:</label>
      <select class="form-select" name="idioma_id" id="idioma_id" value="">
      </select>
    </div>

    <div class="col-md-4">
      <label>Tipus:</label>
      <select class="form-select" name="tipus_id" id="tipus_id"></select>
      </select>
    </div>

    <div class="col-md-4">
      <label>Estat del llibre:</label>
      <select class="form-select" name="estat_id" id="estat_id">
      </select>
    </div>

    <hr>

    <div id="inputEditorial" class="col-md-12"> </div>
    <div id="inputIdioma" class="col-md-12"> </div>

    <hr>
    <h4>Imatge del llibre:</h4>

    <div class="col-md-6">
      <label>Imatge coberta existent:</label>
      <select class="form-select" name="img_id" id="img_id"></select>
    </div>

    <div class="col-md-6">
      <label>O puja una nova imatge:</label>
      <input class="form-control" type="file" name="img_upload" id="img_upload" accept="image/*">
    </div>

    <div class="col-md-6">
      <label>Nom Imatge:</label>
      <input class="form-control" type="text" name="alt" id="alt"></select>
    </div>

    <hr>
    <h4>Autor/a o autors/es del llibre:</h4>
    <div class="col-md-6">
      <label>Autors:</label>

      <div id="autorsContainer"></div>

      <button type="button" class="btn btn-sm btn-secondary mt-2" id="addAutorBtn">
        + Afegir autor
      </button>
    </div>

    <hr>
    <h4>Subgènere del llibre:</h4>
    <div class="col-md-6">
      <label>Tema:</label>
      <div id="temaContainer"></div>
    </div>

    <hr>
    <h4>Col·leccions del llibre:</h4>
    <div class="col-md-6">
      <label>Col·leccions:</label>

      <div id="grupsContainer"></div>

      <button type="button" class="btn btn-sm btn-secondary mt-2" id="addGrupBtn">
        + Afegir col·lecció
      </button>
    </div>

    <hr>
    <h4>Etiquetes del llibre:</h4>
    <div class="col-md-6">
      <label>Etiquetes:</label>

      <div id="etiquetesContainer"></div>

      <button type="button" class="btn btn-sm btn-secondary mt-2" id="addEtiquetaBtn">
        + Afegir etiqueta
      </button>
    </div>

    <hr>
    <div class="col-12 mt-4">
      <div class="d-flex justify-content-between">
        <a id="btnTornar" class="btn btn-secondary" href="#">
          Fitxa llibre
        </a>

        <div class="d-flex gap-2">
          <a
            id="btnVeureFitxa"
            class="btn btn-success d-none"
            href="#">
            Veure fitxa
          </a>

          <button id="btn" type="submit" class="btn btn-primary">
            Afegir
          </button>
        </div>
      </div>
    </div>

  </form>
</div>