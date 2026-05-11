<div class="barraNavegacio"></div>

<div class="container form">
  <h2>Base de dades: Biblioteca</h2>
  <h4 id="titolForm"></h4>

  <div class="alert alert-success" id="missatgeOk" style="display:none"></div>
  <div class="alert alert-danger" id="missatgeErr" style="display:none"></div>

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
      <label>Tema:</label>
      <select class="form-select" name="sub_tema_id" id="sub_tema_id" value="">
      </select>
    </div>

    <div class="col-md-4">
      <label>Idioma:</label>
      <select class="form-select" name="lang" id="lang" value="">
      </select>
    </div>

    <div class="col-md-4">
      <label>Tipus:</label>
      <select class="form-select" name="tipus_id" id="tipus_id"></select>
      </select>
    </div>

    <div class="col-md-4">
      <label>Col·lecció:</label>
      <select class="form-select" name="grup" id="grup" value="">
      </select>
    </div>

    <div class="col-md-4">
      <label>Estat del llibre:</label>
      <select class="form-select" name="estat_id" id="estat_id">
      </select>
    </div>

    <hr>

    <div class="col-md-6">
      <label>Imatge coberta existent:</label>
      <select class="form-select" name="img_id" id="img_id"></select>
    </div>

    <div class="col-md-6">
      <label>O puja una nova imatge:</label>
      <input class="form-control" type="file" name="img_upload" id="img_upload" accept="image/*">
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

    <div class="container" style="margin-top:20px">
      <div class="row">
        <div class="col-6 text-left">
          <a class="btn btn-secondary" href="<?php echo APP_INTRANET . $url['biblioteca']; ?>/llibre-autors/<?php echo htmlspecialchars($slug); ?>">Tornar</a>
        </div>
        <div class="col-6 text-right derecha">
          <button id="btn" type="submit" class="btn btn-primary">Afegir</button>
        </div>
      </div>
    </div>
  </form>
</div>


</div>