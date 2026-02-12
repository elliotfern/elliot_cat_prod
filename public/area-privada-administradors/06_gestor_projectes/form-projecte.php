<div class="barraNavegacio">
</div>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h2>Gestor de projectes</h2>
    <p>
    <h4><span id="titolForm"></span></p>
    </h4>
    <button class="btn btn-outline-secondary" type="button" id="btnBack">← Torna</button>
</div>

<!-- Alerts -->

<div class="alert alert-success" id="okMessage" style="display:none">
    <div id="okText"></div>
</div>
<div class="alert alert-danger" id="errMessage" style="display:none">
    <div id="errText"></div>
</div>

<form id="formProjecte" class="form">
    <input type="hidden" name="id" id="id">

    <div class="mb-3">
        <label class="form-label" for="name">Nom</label>
        <input class="form-control" type="text" name="name" id="name" maxlength="160" required>
    </div>

    <div class="mb-3">
        <label class="form-label" for="description">Descripció</label>
        <textarea class="form-control" name="description" id="description" rows="6"></textarea>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <label class="form-label" for="status">Estat</label>
            <select class="form-select" name="status" id="status">
                <option value="1">Actiu</option>
                <option value="2">En pausa</option>
                <option value="3">Finalitzat</option>
                <option value="0">Arxivat</option>
            </select>
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label" for="priority">Prioritat</label>
            <select class="form-select" name="priority" id="priority">
                <option value="1">Baixa</option>
                <option value="2">Mitja</option>
                <option value="3">Alta</option>
                <option value="4">Molt alta</option>
            </select>
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label" for="category_id">Categoria</label>
            <select class="form-select" name="category_id" id="category_id">
                <option value="">—</option>
            </select>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label" for="start_date">Data inici</label>
            <input class="form-control" type="date" name="start_date" id="start_date">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label" for="end_date">Data fi</label>
            <input class="form-control" type="date" name="end_date" id="end_date">
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label" for="client_id">Client</label>
            <select class="form-select" name="client_id" id="client_id">
                <option value="">—</option>
            </select>
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label" for="budget_id">Budget</label>
            <select class="form-select" name="budget_id" id="budget_id">
                <option value="">—</option>
            </select>
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label" for="invoice_id">Invoice</label>
            <select class="form-select" name="invoice_id" id="invoice_id">
                <option value="">—</option>
            </select>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-end">
        <button id="btnProjecte" class="btn btn-primary" type="submit">Desar</button>
    </div>
</form>