<div id="barraNavegacioContenidor"></div>

<div class="form">

  <h1 class="mb-2">Galeria d'imatges</h1>
  <h4 id="titolForm" class="mb-4"></h4>

  <div id="okMessage" class="alert alert-success d-none" role="alert">
    <span id="okText"></span>
  </div>

  <div id="errMessage" class="alert alert-danger d-none" role="alert">
    <span id="errText"></span>
  </div>

  <form id="galeriaImgForm" class="row g-3">

    <input type="hidden" id="id" name="id" value="">

    <!-- =====================================================
         DATOS DE LA GALERÍA
         ===================================================== -->

    <div class="col-12">
      <h4 class="border-bottom pb-2">
        Dades de la galeria
      </h4>
    </div>

    <!-- Nom de la galeria -->
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


    <!-- Directori -->
    <div class="col-md-4">
      <label for="directori" class="form-label">
        Directori <span class="text-danger">*</span>
      </label>

      <input
        type="text"
        class="form-control"
        id="directori"
        name="directori"
        required>

      <div class="form-text">
        Nom del directori on es guardaran les imatges de la galeria.
      </div>
    </div>

    <!-- Slug galeria -->
    <div class="col-md-4">
      <label for="nom" class="form-label">
        Slug <span class="text-danger">*</span>
      </label>

      <input
        type="text"
        class="form-control"
        id="slug"
        name="slug"
        required>
    </div>

    <!-- Descripció -->
    <div class="col-12">
      <label for="alt" class="form-label">
        Descripció
      </label>

      <textarea
        class="form-control"
        id="alt"
        name="alt"
        rows="4"></textarea>
    </div>

    <div class="form-check mb-3">
      <input
        type="checkbox"
        class="form-check-input"
        id="publica"
        name="publica"
        value="1">

      <label
        class="form-check-label"
        for="publica">
        Galeria pública
      </label>
    </div>


    <!-- =====================================================
         IMATGES DE LA GALERIA
         ===================================================== -->

    <div class="col-12 mt-4">

      <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">

        <h4 class="mb-0">
          Imatges de la galeria
        </h4>

        <button
          type="button"
          id="btnAfegirImatge"
          class="btn btn-primary">
          Afegir imatge
        </button>

      </div>

      <!--
        TypeScript crearà aquí els blocs de les imatges.
      -->
      <div
        id="imatgesContainer"
        class="row g-4">
      </div>

    </div>


    <!-- =====================================================
         BOTONS
         ===================================================== -->

    <div class="col-12 mt-4">

      <div class="d-flex justify-content-between align-items-center">

        <a
          id="btnTornar"
          class="btn btn-secondary"
          href="#">
          Galeries
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
            Crear galeria
          </button>

        </div>

      </div>

    </div>

  </form>

</div>