<div id="barraNavegacioContenidor"></div>

<div class="form py-4">

    <h2 class="mb-4">Base de dades: ciutats</h2>

    <div id="titolForm" class="mb-3"></div>

    <div class="alert alert-success d-none" id="okMessage" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger d-none" id="errMessage" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" class="row g-3" id="formCiutat">

        <input type="hidden" name="id" id="id" value="">

        <div class="col-md-4">
            <label for="ciutat" class="form-label">Nom *</label>
            <input
                type="text"
                class="form-control"
                id="ciutat"
                name="ciutat"
                maxlength="150"
                required>
            <div class="invalid-feedback">
                Obligatori.
            </div>
        </div>

        <div class="col-md-4">
            <label for="pais_id" class="form-label">País *</label>
            <select
                class="form-select"
                id="pais_id"
                name="pais_id"
                required></select>
        </div>

        <div class="col-12">
            <label for="descripcio" class="form-label">Descripció</label>
            <textarea
                id="descripcio"
                name="descripcio"
                class="form-control"
                rows="4"
                maxlength="2000"></textarea>
        </div>

        <div class="col-12 d-flex justify-content-end mt-4">
            <button
                type="submit"
                class="btn btn-primary"
                id="btnCiutat">
                Introduir dades
            </button>
        </div>

    </form>

</div>