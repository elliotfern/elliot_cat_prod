<div class="barraNavegacio">
</div>

<div class="container-fluid form">

    <h2>Base de dades: ciutats</h2>
    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>

    </div>

    <form method="POST" action="" class="row g-3" id="formCiutat" data-success-redirect-template="/gestio/base-dades-persones/fitxa-persona/{slug}">

        <input type="hidden" name="id" id="id" value="">

        <div class="col-md-4">
            <label for="ciutat_ca" class="form-label">Nom (català) *</label>
            <input type="text" class="form-control" id="ciutat_ca" name="ciutat_ca" required maxlength="150" />
            <div class="invalid-feedback">Obligatori.</div>
        </div>
        <div class="col-md-4">
            <label for="ciutat_en" class="form-label">Nom (anglès)</label>
            <input type="text" class="form-control" id="ciutat_en" name="ciutat_en" maxlength="150" />
        </div>

        <div class="col-md-4">
            <label for="pais_id" class="form-label">País *</label>
            <select class="form-select" id="pais_id" name="pais_id" required></select>
        </div>

        <div class="col-complet">
            <label for="descripcio" class="form-label">Notes</label>
            <textarea id="descripcio" name="descripcio" class="form-control" rows="4" maxlength="2000"></textarea>
        </div>

        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left">

                </div>
                <div class="col-6 text-right derecha">
                    <button type="submit" class="btn btn-primary" id="btnCiutat">Introduir dades</button>
                </div>
            </div>
        </div>
    </form>

</div>