<div class="barraNavegacio">
</div>

<div class="container-fluid form">

    <h2>Base de dades: Paisos</h2>
    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>

    </div>

    <form method="POST" action="" class="row g-3" id="formPais" data-success-redirect-template="/gestio/base-dades-persones/fitxa-persona/{slug}">

        <input type="hidden" name="id" id="id" value="">

        <input type="hidden" id="id" name="id"
            pattern="^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$" />

        <div class="col-md-4">
            <label for="pais_ca" class="form-label">País (català) *</label>
            <input type="text" class="form-control" id="pais_ca" name="pais_ca" required maxlength="150" />
            <div class="invalid-feedback">Camp obligatori.</div>
        </div>

        <div class="col-md-4">
            <label for="pais_en" class="form-label">País (anglès)</label>
            <input type="text" class="form-control" id="pais_en" name="pais_en" maxlength="150" />
        </div>

        <div class="col-md-4">

        </div>

        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left">

                </div>
                <div class="col-6 text-right derecha">
                    <button type="submit" class="btn btn-primary" id="btnPais">Introduir dades</button>
                </div>
            </div>
        </div>
    </form>

</div>