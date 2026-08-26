<div id="barraNavegacioContenidor"></div>

<div class="form">

    <h2>Gestió comptabilitat</h2>

    <div id="titolForm"></div>

    <div class="alert alert-success d-none" id="okMessage" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger d-none" id="errMessage" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" class="row g-3" id="formDespesa">

        <input type="hidden" id="id" name="id">

        <div class="col-md-4">
            <label for="data" class="form-label">Data factura:</label>
            <input
                class="form-control"
                type="date"
                name="data"
                id="data"
                required>
        </div>

        <div class="col-md-4">
            <label for="data_pagament" class="form-label">Data pagament:</label>
            <input
                class="form-control"
                type="date"
                name="data_pagament"
                id="data_pagament">
        </div>

        <div class="col-md-4">
            <label for="concepte" class="form-label">Concepte:</label>
            <input
                class="form-control"
                type="text"
                name="concepte"
                id="concepte"
                required>
        </div>

        <div class="col-md-6">
            <label for="proveidor_id" class="form-label">Proveïdor:</label>
            <select
                class="form-select"
                name="proveidor_id"
                id="proveidor_id"
                required>
                <option value="">-- Selecciona proveïdor --</option>
                <!-- Opcions carregades per TS -->
            </select>
        </div>

        <div class="col-md-6">
            <label for="receptor_id" class="form-label">Receptor:</label>
            <select
                class="form-select"
                name="receptor_id"
                id="receptor_id"
                required>
                <option value="">-- Selecciona receptor --</option>
                <!-- Opcions carregades per TS -->
            </select>
        </div>

        <hr>
        <h4>Imports:</h4>

        <div class="col-md-3">
            <label for="base_imposable" class="form-label">
                Base imposable (€):
            </label>
            <input
                class="form-control"
                type="number"
                step="0.01"
                name="base_imposable"
                id="base_imposable"
                required>
        </div>

        <div class="col-md-3">
            <label for="tipus_iva" class="form-label">
                Tipus IVA (%):
            </label>
            <input
                class="form-control"
                type="number"
                step="0.01"
                name="tipus_iva"
                id="tipus_iva"
                required>
        </div>

        <div class="col-md-3">
            <label for="import_iva" class="form-label">
                Import IVA (€):
            </label>
            <input
                class="form-control"
                type="number"
                step="0.01"
                name="import_iva"
                id="import_iva">
        </div>

        <div class="col-md-3">
            <label for="total" class="form-label">
                Total (€):
            </label>
            <input
                class="form-control"
                type="number"
                step="0.01"
                name="total"
                id="total">
        </div>

        <hr>

        <div class="col-md-6">
            <label for="metode_pagament" class="form-label">
                Mètode de pagament:
            </label>
            <select
                class="form-select"
                name="metode_pagament"
                id="metode_pagament">
                <option value="">-- Selecciona mètode --</option>
                <!-- Opcions carregades per TS -->
            </select>
        </div>

        <div class="col-md-3">
            <label for="pagat" class="form-label">Pagat:</label>
            <select
                class="form-select"
                name="pagat"
                id="pagat">
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="tipus_despesa" class="form-label">
                Tipus despesa:
            </label>
            <select
                class="form-select"
                name="tipus_despesa"
                id="tipus_despesa">
            </select>
        </div>

        <div class="col-md-6">
            <label for="categoria_id" class="form-label">
                Categoria:
            </label>
            <select
                class="form-select"
                name="categoria_id"
                id="categoria_id">
                <option value="">-- Selecciona categoria --</option>
                <!-- Opcions carregades per TS -->
            </select>
        </div>

        <div class="col-md-6">
            <label for="subcategoria_id" class="form-label">
                Subcategoria:
            </label>
            <select
                class="form-select"
                name="subcategoria_id"
                id="subcategoria_id">
                <option value="">-- Selecciona subcategoria --</option>
                <!-- Opcions carregades per TS -->
            </select>
        </div>

        <div class="col-12">
            <label for="arxiu_url" class="form-label">
                Arxiu URL:
            </label>
            <input
                class="form-control"
                type="text"
                name="arxiu_url"
                id="arxiu_url">
        </div>

        <div class="col-md-3">
            <label for="deduible" class="form-label">Deduïble:</label>
            <select
                class="form-select"
                name="deduible"
                id="deduible">
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="recurrent" class="form-label">Recurrent:</label>
            <select
                class="form-select"
                name="recurrent"
                id="recurrent">
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="frequencia" class="form-label">
                Freqüència:
            </label>
            <select
                class="form-select"
                name="frequencia"
                id="frequencia">
            </select>
        </div>

        <div class="col-12">
            <label for="notes" class="form-label">Notes:</label>
            <textarea
                class="form-control"
                name="notes"
                id="notes"
                rows="4"></textarea>
        </div>

        <div class="col-12 d-flex justify-content-end mt-4">
            <button
                type="submit"
                class="btn btn-primary"
                id="btnDespesa">
                Desar Despesa
            </button>
        </div>

    </form>
</div>