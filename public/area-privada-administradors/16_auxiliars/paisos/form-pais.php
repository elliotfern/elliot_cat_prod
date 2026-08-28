<div id="barraNavegacioContenidor">
</div>

<div class="form">

    <h2>Base de dades: Paisos</h2>
    <div id="titolForm"></div>

    <div class="alert alert-success d-none" id="okMessage" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger d-none" id="errMessage" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" class="row g-3" id="formPais">

        <input type="hidden" name="id" id="id" value="">

        <div class="col-12 col-md-6">
            <label for="pais" class="form-label">País *</label>
            <input
                type="text"
                class="form-control"
                id="pais"
                name="pais"
                required
                maxlength="150">
            <div class="invalid-feedback">
                Camp obligatori.
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end mt-3">
            <button
                type="submit"
                class="btn btn-primary"
                id="btnPais">
                Introduir dades
            </button>
        </div>

    </form>


</div>