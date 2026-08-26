<div id="barraNavegacioContenidor"></div>

<div class="container-fluid form">

    <h2>Gestió comptabilitat: Catàleg de productes</h2>

    <div id="titolForm"></div>

    <div class="alert alert-success d-none" id="okMessage" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger d-none" id="errMessage" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" class="row g-3" id="formProducte">

        <input type="hidden" id="id" name="id">

        <div class="col-md-6">
            <label for="producte" class="form-label">Producte:</label>
            <input
                class="form-control"
                type="text"
                name="producte"
                id="producte"
                required>
        </div>

        <div class="col-md-4">
        </div>

        <div class="col-md-4">
            <label for="unitat" class="form-label">Unitat:</label>
            <input
                class="form-control"
                type="text"
                name="unitat"
                id="unitat"
                placeholder="Ex: hora, dia, projecte...">
        </div>

        <div class="col-md-4">
            <label for="preu_recomanat" class="form-label">
                Preu recomanat (€):
            </label>
            <input
                class="form-control"
                type="number"
                step="0.01"
                name="preu_recomanat"
                id="preu_recomanat">
        </div>

        <div class="col-md-4">
            <label for="actiu" class="form-label">Actiu:</label>
            <select
                class="form-select"
                name="actiu"
                id="actiu">
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </div>

        <div class="col-12">
            <label for="descripcio" class="form-label">
                Descripció:
            </label>
            <textarea
                class="form-control"
                name="descripcio"
                id="descripcio"
                rows="4"></textarea>
        </div>

        <div class="col-12 d-flex justify-content-end mt-4">
            <button
                type="submit"
                class="btn btn-primary"
                id="btnProducte">
                Desar Producte
            </button>
        </div>

    </form>
</div>