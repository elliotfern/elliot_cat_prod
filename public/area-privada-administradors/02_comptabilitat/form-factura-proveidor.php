<div class="barraNavegacio"></div>

<div class="container-fluid form">

    <h2>Gestió comptabilitat: Factura de despesa</h2>
    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" class="row g-3" id="formDespesa">

        <input type="hidden" id="id" name="id" />

        <div class="col-md-4">
            <label for="data">Data factura:</label>
            <input class="form-control" type="date" name="data" id="data" required />
        </div>

        <div class="col-md-4">
            <label for="data_pagament">Data pagament:</label>
            <input class="form-control" type="date" name="data_pagament" id="data_pagament" />
        </div>

        <div class="col-md-4">
            <label for="concepte">Concepte:</label>
            <input class="form-control" type="text" name="concepte" id="concepte" required />
        </div>

        <div class="col-md-6">
            <label for="proveidor_id">Proveïdor:</label>
            <select class="form-select" name="proveidor_id" id="proveidor_id" required>
                <option value="">-- Selecciona proveïdor --</option>
                <!-- Opcions carregades per TS -->
            </select>
        </div>

        <div class="col-md-6">
            <label for="receptor_id">Receptor:</label>
            <select class="form-select" name="receptor_id" id="receptor_id" required>
                <option value="">-- Selecciona proveïdor --</option>
                <!-- Opcions carregades per TS -->
            </select>
        </div>

        <div class="col-md-3">
            <label for="base_imposable">Base imposable (€):</label>
            <input class="form-control" type="number" step="0.01" name="base_imposable" id="base_imposable" required />
        </div>

        <div class="col-md-3">
            <label for="tipus_iva">Tipus IVA (%):</label>
            <input class="form-control" type="number" step="0.01" name="tipus_iva" id="tipus_iva" required />
        </div>

        <div class="col-md-3">
            <label for="import_iva">Import IVA (€):</label>
            <input class="form-control" type="number" step="0.01" name="import_iva" id="import_iva" />
        </div>

        <div class="col-md-3">
            <label for="total">Total (€):</label>
            <input class="form-control" type="number" step="0.01" name="total" id="total" />
        </div>

        <div class="col-md-6">
            <label for="metode_pagament">Mètode de pagament:</label>
            <select class="form-select" name="metode_pagament" id="metode_pagament">
                <option value="">-- Selecciona mètode --</option>
                <!-- Opcions carregades per TS -->
            </select>
        </div>

        <div class="col-md-3">
            <label for="pagat">Pagat:</label>
            <select class="form-select" name="pagat" id="pagat">
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="tipus_despesa">Tipus despesa:</label>
            <select class="form-select" name="tipus_despesa" id="tipus_despesa">
            </select>
        </div>

        <div class="col-md-6">
            <label for="categoria_id">Categoria:</label>
            <select class="form-select" name="categoria_id" id="categoria_id">
                <option value="">-- Selecciona proveïdor --</option>
                <!-- Opcions carregades per TS -->
            </select>
        </div>

        <div class="col-md-6">
            <label for="subcategoria_id">Subcategoria:</label>

            <select class="form-select" name="subcategoria_id" id="subcategoria_id">
                <option value="">-- Selecciona proveïdor --</option>
                <!-- Opcions carregades per TS -->
            </select>
        </div>

        <div class="col-md-6">
            <label for="client_id">Client (opcional):</label>

            <select class="form-select" name="client_id" id="client_id">
                <option value="">-- Selecciona proveïdor --</option>
                <!-- Opcions carregades per TS -->
            </select>
        </div>

        <div class="col-md-6">
            <label for="projecte_id">Projecte (opcional):</label>

            <select class="form-select" name="projecte_id" id="projecte_id">
                <option value="">-- Selecciona proveïdor --</option>
                <!-- Opcions carregades per TS -->
            </select>
        </div>

        <div class="col-12">
            <label for="arxiu_url">Arxiu URL:</label>
            <input class="form-control" type="text" name="arxiu_url" id="arxiu_url" />
        </div>

        <div class="col-md-3">
            <label for="deduible">Deduïble:</label>
            <select class="form-select" name="deduible" id="deduible">
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="recurrent">Recurrent:</label>
            <select class="form-select" name="recurrent" id="recurrent">
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="frequencia">Freqüència:</label>
            <select class="form-select" name="frequencia" id="frequencia">
            </select>
        </div>

        <div class="col-12">
            <label for="notes">Notes:</label>
            <textarea class="form-control" name="notes" id="notes" rows="4"></textarea>
        </div>

        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left">
                    <!-- Espai per botons extra -->
                </div>
                <div class="col-6 text-right derecha">
                    <button type="submit" class="btn btn-primary" id="btnDespesa">Desar Despesa</button>
                </div>
            </div>
        </div>

    </form>
</div>