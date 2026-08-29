<div id="barraNavegacioContenidor"></div>

<div class="d-flex align-items-start justify-content-between mb-3">
    <div class="d-flex flex-column">
        <h2 class="mb-1">Gestor de projectes</h2>
        <h4 class="mb-0">
            <span id="titolForm"></span>
        </h4>
    </div>

    <button class="btn btn-outline-secondary" type="button" id="btnBack">
        ← Torna
    </button>
</div>

<!-- Alerts -->

<div class="alert alert-success d-none" id="okMessage">
    <div id="okText"></div>
</div>
<div class="alert alert-danger d-none" id="errMessage">
    <div id="errText"></div>
</div>

<form id="formProjecte" class="form">
    <input type="hidden" name="id" id="id">

    <div class="mb-3">
        <label class="form-label" for="projecte">Nom</label>
        <input class="form-control" type="text" name="projecte" id="projecte" maxlength="160" required>
    </div>

    <div class="mb-3">
        <label class="form-label" for=" ">Descripció</label>
        <textarea class="form-control" name="descripcio" id="descripcio" rows="6"></textarea>
    </div>

    <div class="row g-3">

        <div class="col-4 col-md-4">
            <label for="estat" class="form-label">Estat</label>
            <select class="form-select" id="estat" name="estat">
            </select>
        </div>

        <div class="col-4 col-md-4">
            <label for="prioritat" class="form-label">Prioritat</label>
            <select class="form-select" id="prioritat" name="prioritat">
            </select>
        </div>

        <div class="col-4 col-md-4">
            <label for="categoria" class="form-label">Categoria</label>
            <select class="form-select" id="categoria" name="categoria">>
            </select>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label" for="data_inici">Data inici</label>
            <input class="form-control" type="date" name="data_inici" id="data_inici">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label" for="data_fi">Data fi</label>
            <input class="form-control" type="date" name="data_fi" id="data_fi">
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label" for="client_id">Client</label>
            <select class="form-select" name="client_id" id="client_id">
                <option value="">—</option>
            </select>
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label" for="pressupost_id">Pressupost</label>
            <select class="form-select" name="pressupost_id" id="pressupost_id">
                <option value="">—</option>
            </select>
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label" for="factura_id">Factura</label>
            <select class="form-select" name="factura_id" id="factura_id">
                <option value="">—</option>
            </select>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-end">
        <button id="btnProjecte" class="btn btn-primary" type="submit">Desar</button>
    </div>
</form>