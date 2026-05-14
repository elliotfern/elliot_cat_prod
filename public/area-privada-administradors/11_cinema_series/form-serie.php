<div class="barraNavegacioContenidor"></div>

<div class="container form">

  <h2>Base de dades de sèries de televisió</h2>

  <div id="titolForm" class="mb-4"></div>

  <div class="alert alert-success" id="missatgeOk" style="display:none" role="alert">
    <div id="okText"></div>
  </div>

  <div class="alert alert-danger" id="missatgeErr" style="display:none" role="alert">
    <div id="errText"></div>
  </div>

  <form method="POST" action="" id="formSerie">

    <input type="hidden" name="id" id="id" value="">

    <div class="row g-3">

      <!-- Nom -->
      <div class="col-12 col-md-6">
        <label for="name" class="form-label">
          Nom de la sèrie
        </label>

        <input
          class="form-control"
          type="text"
          name="name"
          id="name"
          maxlength="255"
          required>
      </div>

      <!-- Slug -->
      <div class="col-12 col-md-6">
        <label for="slug" class="form-label">
          Slug URL
        </label>

        <input
          class="form-control"
          type="text"
          name="slug"
          id="slug"
          maxlength="255"
          required>
      </div>

      <!-- Any inici -->
      <div class="col-12 col-md-3">
        <label for="startYear" class="form-label">
          Any d'inici
        </label>

        <input
          class="form-control"
          type="number"
          name="startYear"
          id="startYear"
          min="1900"
          max="2100">
      </div>

      <!-- Any final -->
      <div class="col-12 col-md-3">
        <label for="endYear" class="form-label">
          Any final
        </label>

        <input
          class="form-control"
          type="number"
          name="endYear"
          id="endYear"
          min="1900"
          max="2100">
      </div>

      <!-- Temporades -->
      <div class="col-12 col-md-3">
        <label for="season" class="form-label">
          Número de temporades
        </label>

        <input
          class="form-control"
          type="number"
          name="season"
          id="season"
          min="1">
      </div>

      <!-- Capítols -->
      <div class="col-12 col-md-3">
        <label for="chapter" class="form-label">
          Número de capítols
        </label>

        <input
          class="form-control"
          type="number"
          name="chapter"
          id="chapter"
          min="1">
      </div>

      <!-- Director -->
      <div class="col-12 col-md-4">
        <label for="director_id" class="form-label">
          Director
        </label>

        <select
          class="form-select"
          name="director_id"
          id="director_id">
        </select>
      </div>

      <!-- Gènere -->
      <div class="col-12 col-md-4">
        <label for="genere_id" class="form-label">
          Gènere
        </label>

        <select
          class="form-select"
          name="genere_id"
          id="genere_id">
        </select>
      </div>

      <!-- País -->
      <div class="col-12 col-md-4">
        <label for="pais_id" class="form-label">
          País
        </label>

        <select
          class="form-select"
          name="pais_id"
          id="pais_id">
        </select>
      </div>

      <!-- Idioma -->
      <div class="col-12 col-md-4">
        <label for="idioma_id" class="form-label">
          Idioma original
        </label>

        <select
          class="form-select"
          name="idioma_id"
          id="idioma_id">
        </select>
      </div>

      <!-- Imatge existent -->
      <div class="col-12 col-md-4">
        <label for="img_id" class="form-label">
          Imatge existent
        </label>

        <select
          class="form-select"
          name="img_id"
          id="img_id">
        </select>
      </div>

      <!-- Upload imatge -->
      <div class="col-12 col-md-4">
        <label for="img_upload" class="form-label">
          Pujar nova imatge
        </label>

        <input
          class="form-control"
          type="file"
          name="img_upload"
          id="img_upload"
          accept="image/*">
      </div>

      <!-- Alt imatge -->
      <div class="col-12">
        <label for="alt" class="form-label">
          Text alternatiu de la imatge
        </label>

        <input
          class="form-control"
          type="text"
          name="alt"
          id="alt"
          maxlength="255">
      </div>

      <!-- Descripció -->
      <div class="col-12">
        <label for="descripcio" class="form-label">
          Descripció / crítica
        </label>

        <textarea
          class="form-control"
          id="descripcio"
          name="descripcio"
          rows="8"
          required></textarea>
      </div>

      <hr>

      <div class="col-12">

        <h4>Actors i actrius de la sèrie</h4>

        <div id="actorsContainer"></div>

        <button
          type="button"
          class="btn btn-sm btn-secondary mt-2"
          id="addActorBtn">
          + Afegir actor/a
        </button>

      </div>

    </div>

    <div class="row mt-4">

      <div class="col-12 col-md-6 mb-2 mb-md-0">
        <a
          href="#"
          onclick="window.history.back()"
          class="btn btn-outline-secondary w-100 w-md-auto">
          Tornar enrere
        </a>
      </div>

      <div class="col-12 col-md-6 text-md-end">

        <button
          type="submit" id="btnForm"
          class="btn btn-primary w-100 w-md-auto">
          Desa les dades
        </button>

      </div>

    </div>

  </form>

</div>