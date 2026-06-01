<div class="container-fluid form">

    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none">
        <div id="okText"></div>
    </div>
    <div class="alert alert-danger" id="errMessage" style="display:none">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formCVExperiencia" class="row g-3">

        <!-- PK (autoincrement). Buit en crear; ple en editar -->
        <input type="hidden" id="id" name="id" value="">

        <!-- Empresa -->
        <div class="col-md-4">
            <label for="empresa" class="form-label">Empresa *</label>
            <input class="form-control" type="text" id="empresa" name="empresa" maxlength="190" required>
        </div>

        <!-- URL empresa -->
        <div class="col-md-4">
            <label for="empresa_url" class="form-label">Web de l'empresa</label>
            <input class="form-control" type="url" id="empresa_url" name="empresa_url" maxlength="255" placeholder="https://...">
        </div>

        <!-- Localització -->
        <div class="col-md-4">
            <label for="empresa_localitzacio" class="form-label">Localització</label>
            <select class="form-select" name="empresa_localitzacio" id="empresa_localitzacio">
                <!-- Si tens catàleg, carrega les opcions per API; placeholders: -->
            </select>
        </div>

        <!-- Data inici -->
        <div class="col-md-4">
            <label for="data_inici" class="form-label">Data d'inici *</label>
            <input class="form-control" type="date" id="data_inici" name="data_inici" required>
        </div>

        <!-- Data fi -->
        <div class="col-md-4">
            <label for="data_fi" class="form-label">Data de fi</label>
            <input class="form-control" type="date" id="data_fi" name="data_fi">
            <div class="form-text">Deixa en blanc si encara hi treballes.</div>
        </div>

        <!-- Logo empresa -->
        <div class="col-md-4">
            <label for="logo_empresa" class="form-label">Logo empresa</label>
            <select class="form-select" name="logo_empresa" id="logo_empresa">
                <!-- Si tens catàleg, carrega les opcions per API; placeholders: -->
            </select>
        </div>

        <!-- Posició d’ordre -->
        <div class="col-md-4">
            <label for="posicio" class="form-label">Posició</label>
            <input class="form-control" type="number" id="posicio" name="posicio" value="0" step="1">
            <div class="form-text">Ordenació manual (0, 1, 2...).</div>
        </div>

        <hr class="col-complet">

        <!-- Actual (is_current) -->
        <div class="col-md-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_current" name="is_current" value="1">
                <label class="form-check-label" for="is_current">Treball actual</label>
            </div>
        </div>

        <!-- Visible -->
        <div class="col-md-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="visible" name="visible" value="1">
                <label class="form-check-label" for="visible">Visible públicament</label>
            </div>
        </div>

        <div class="col-md-4">

        </div>

        <!-- Botó -->
        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left"></div>
                <div class="col-6 text-right derecha">
                    <button type="submit" id="btnExperiencia" class="btn btn-primary">Desa experiència</button>
                </div>
            </div>
        </div>
    </form>

</div>