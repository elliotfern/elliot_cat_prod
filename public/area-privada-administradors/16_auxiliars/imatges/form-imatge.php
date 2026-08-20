<div id="barraNavegacioContenidor"></div>

<div class="form">

  <h1 class="mb-2">Base de dades: Auxiliars</h1>
  <h4 id="titolForm" class="mb-4"></h4>

  <div id="okMessage" class="alert alert-success d-none" role="alert">
    <span id="okText"></span>
  </div>

  <div id="errMessage" class="alert alert-danger d-none" role="alert">
    <span id="errText"></span>
  </div>

  <form id="uploadImgForm" class="row g-3">

    <input type="hidden" id="id" name="id" value="">

    <!-- Nom -->
    <div class="col-md-4">
      <label for="nom" class="form-label">
        Nom <span class="text-danger">*</span>
      </label>

      <input
        type="text"
        class="form-control"
        id="nom"
        name="nom"
        required>
    </div>

    <!-- Categoria -->
    <div class="col-md-4">
      <label for="typeImg" class="form-label">
        Categoria de la imatge
      </label>

      <select
        class="form-select"
        id="typeImg"
        name="typeImg">
        <option value="" selected>Selecciona el tipus d'imatge</option>
        <option value="1">Persona</option>
        <option value="2">Biblioteca llibres: llibre</option>
        <option value="3">Història: imatge</option>
        <option value="4">Història: esdeveniment</option>
        <option value="6">Història: organització</option>
        <option value="12">Història: mapa</option>
        <option value="15">Història: infografia</option>
        <option value="16">Història: cronologia</option>
        <option value="10">Història: thumbnail</option>
        <option value="7">Cinema: sèrie tv</option>
        <option value="8">Cinema: pel·lícula</option>
        <option value="11">Viatges: viatge</option>
        <option value="17">Viatges: espai</option>
        <option value="13">Blog: imatges</option>
        <option value="18">Usuaris: avatars</option>
        <option value="19">Web: icones</option>
        <option value="20">CV: logos empreses</option>
      </select>
    </div>

    <!--  -->
    <div class="col-md-4">
    </div>

    <hr>

    <!-- Data foto -->
    <div class="col-md-4">
      <label for="dataImatge" class="form-label">
        Data imatge (automàtic)
      </label>

      <input
        type="datetime-local"
        class="form-control"
        id="dataImatge"
        name="dataImatge">
    </div>

    <!-- Any foto -->
    <div class="col-md-4">
      <label for="any" class="form-label">
        Any (opcional)
      </label>

      <input
        type="number"
        class="form-control"
        id="any"
        name="any"
        min="1900"
        max="2040">
    </div>

    <hr>

    <!-- Descripció -->
    <div class="col-12">
      <label for="alt" class="form-label">
        Descripció de la imatge
      </label>

      <textarea
        class="form-control"
        id="alt"
        name="alt"
        rows="5"></textarea>
    </div>

    <!-- Fitxer -->
    <div class="col-md-6 col-lg-4">
      <label for="fileToUpload" class="form-label">
        Fitxer
      </label>

      <input
        type="file"
        class="form-control"
        id="fileToUpload"
        name="fileToUpload">
    </div>

    <!-- Botons -->
    <div class="col-12 mt-4">
      <div class="d-flex justify-content-between align-items-center">

        <a
          id="btnTornar"
          class="btn btn-secondary"
          href="#">
          Fitxa Imatge
        </a>

        <div class="d-flex gap-2">

          <a
            id="btnVeureFitxa"
            class="btn btn-success d-none"
            href="#">
            Veure fitxa
          </a>

          <button
            id="btnForm"
            type="submit"
            class="btn btn-primary">
            Afegir
          </button>

        </div>
      </div>
    </div>

  </form>
</div>