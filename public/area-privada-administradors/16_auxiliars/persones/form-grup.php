<div class="barraNavegacio">
</div>

<div class="container-fluid form">

    <h2>Base de dades: persones</h2>
    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>
    </div>

    <form
        action=""
        class="row g-3"
        id="formGrupPersones">

        <!-- id BINARY(16) (en UI lo tratamos como hex/uuid string) -->
        <input type="hidden" name="id" id="id" value="">

        <div class="col-md-4">
            <label for="grup_ca" class="form-label">Nom (català) *</label>
            <input type="text" class="form-control" id="grup_ca" name="grup_ca" required maxlength="150" value="" />
            <div class="invalid-feedback">Obligatori.</div>
        </div>

        <div class="col-md-4">
            <label for="grup_es" class="form-label">Nom (castellà) *</label>
            <input type="text" class="form-control" id="grup_es" name="grup_es" value="" maxlength="150" />
            <div class="invalid-feedback">Obligatori.</div>
        </div>

        <div class="col-md-4">
            <label for="grup_en" class="form-label">Nom (anglès) *</label>
            <input type="text" class="form-control" id="grup_en" name="grup_en" value="" maxlength="150" />
            <div class="invalid-feedback">Obligatori.</div>
        </div>

        <div class="col-md-4">
            <label for="grup_it" class="form-label">Nom (italià) *</label>
            <input type="text" class="form-control" id="grup_it" name="grup_it" value="" maxlength="150" />
            <div class="invalid-feedback">Obligatori.</div>
        </div>

        <div class="col-md-4">
            <label for="grup_fr" class="form-label">Nom (francès) *</label>
            <input type="text" class="form-control" id="grup_fr" name="grup_fr" value="" maxlength="150" />
            <div class="invalid-feedback">Obligatori.</div>
        </div>

        <!-- (opcional) columna vacía para cuadrar la fila si quieres -->
        <div class="col-md-4"></div>

        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left">
                    <!-- aquí si quieres luego: botón borrar, volver, etc -->
                </div>
                <div class="col-6 text-right derecha">
                    <button type="submit" class="btn btn-primary" id="btnGrupPersones">Introduir dades</button>
                </div>
            </div>
        </div>
    </form>


</div>