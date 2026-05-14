<div class="barraNavegacioContenidor"></div>

<div class="container form">

  <h2>Base de dades de pel·lícules</h2>

  <div id="titolForm" class="mb-4"></div>

  <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
    <div id="okText"></div>
  </div>

  <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
    <div id="errText"></div>
  </div>

  <form method="POST" action="" id="formPelicula">

    <input type="hidden" name="id" id="id" value="">

    <div class="row g-3">

      <!-- Títol original -->
      <div class="col-12 col-md-6">
        <label for="pelicula" class="form-label">
          Títol original
        </label>

        <input
          class="form-control"
          type="text"
          name="pelicula"
          id="pelicula"
          maxlength="255"
          required>
      </div>

      <!-- Títol català -->
      <div class="col-12 col-md-6">
        <label for="pelicula_ca" class="form-label">
          Títol en català
        </label>

        <input
          class="form-control"
          type="text"
          name="pelicula_ca"
          id="pelicula_ca"
          maxlength="255">
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

      <!-- Any -->
      <div class="col-12 col-md-3">
        <label for="any" class="form-label">
          Any
        </label>

        <input
          class="form-control"
          type="number"
          name="any"
          id="any"
          min="1888"
          max="2100">
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
        <label for="imatge_id" class="form-label">
          Imatge existent
        </label>

        <select
          class="form-select"
          name="imatge_id"
          id="imatge_id">
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

      <!-- ACTORS -->
      <div class="col-12">

        <h4>Actors i actrius de la pel·lícula</h4>

        <div id="actorsContainer"></div>

        <button
          type="button"
          class="btn btn-sm btn-secondary mt-2"
          id="addActorBtn">
          + Afegir actor/a
        </button>

      </div>

    </div>

    <!-- BOTONS -->
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
          type="submit"
          id="btnForm"
          class="btn btn-primary w-100 w-md-auto">
          Desa les dades
        </button>

      </div>

    </div>

  </form>

</div>