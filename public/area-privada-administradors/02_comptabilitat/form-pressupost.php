<?php

use App\Utils\Url;
?>

<div id="barraNavegacioContenidor"></div>

<h1>Gestió Comptabilitat i Clients</h1>
<h2>Formulari Pressupost: <span id="titolForm"></span>
</h2>

<div class="form">
    <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formPressupost" class="needs-validation" novalidate>

        <input type="hidden" id="id" name="id" />

        <div class="row g-3">

            <!-- CONCEPTE -->
            <div class="col-md-6">
                <label for="concepte" class="form-label">Concepte *</label>
                <input type="text"
                    class="form-control"
                    id="concepte"
                    name="concepte"
                    required
                    maxlength="255" />
                <div class="invalid-feedback" id="error-concepte"></div>
            </div>

            <!-- IMPORT -->
            <div class="col-md-3">
                <label for="import" class="form-label">Import *</label>
                <input type="number"
                    step="0.01"
                    class="form-control"
                    id="import"
                    name="import"
                    required />
                <div class="invalid-feedback" id="error-import"></div>
            </div>

            <!-- DATA -->
            <div class="col-md-3">
                <label for="data" class="form-label">Data *</label>
                <input type="date"
                    class="form-control"
                    id="data"
                    name="data"
                    required />
                <div class="invalid-feedback" id="error-data"></div>
            </div>

            <!-- CLIENT -->
            <div class="col-md-4">
                <label for="client_id" class="form-label">Client *</label>
                <select class="form-select"
                    id="client_id"
                    name="client_id"
                    required></select>
                <div class="invalid-feedback" id="error-client_id"></div>
            </div>

            <!-- SERVEI -->
            <div class="col-md-4">
                <label for="servei_id" class="form-label">Servei *</label>
                <select class="form-select"
                    id="servei_id"
                    name="servei_id"
                    required></select>
                <div class="invalid-feedback" id="error-servei_id"></div>
            </div>

            <!-- ESTAT -->
            <div class="col-md-4">
                <label for="estat_id" class="form-label">Estat *</label>
                <select class="form-select"
                    id="estat_id"
                    name="estat_id"
                    required></select>
                <div class="invalid-feedback" id="error-estat_id"></div>
            </div>

        </div>

        <!-- BOTONS -->
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">

            <a href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-pressupostos"
                class="btn btn-outline-secondary">
                ← Tornar enrere
            </a>

            <button type="submit"
                class="btn btn-primary"
                id="btnPressupost">
                Guardar pressupost
            </button>

        </div>

    </form>
</div>