<?php

use App\Utils\Url;
?>

<div id="barraNavegacioContenidor"></div>

<h1>Gestió Comptabilitat i Clients</h1>
<h2>Formulari: <span id="titolForm"></span></h2>

<div class="form">
    <div class="alert alert-success d-none" id="okMessage" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger d-none" id="errMessage" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formClient" class="needs-validation" novalidate>

        <input type="hidden" id="id" name="id">

        <div class="row g-3">

            <!-- CLIENT ID -->
            <div class="col-md-4">
                <label for="ciutat_id" class="form-label">Client *</label>
                <select
                    class="form-select"
                    id="contacte_id"
                    name="contacte_id"
                    required></select>
                <div class="invalid-feedback" id="error-contacte_id"></div>
            </div>

            <!-- ESTAT -->
            <div class="col-md-4">
                <label for="estat_id" class="form-label">Estat *</label>
                <select
                    class="form-select"
                    id="estat_id"
                    name="estat_id"
                    required></select>
                <div class="invalid-feedback" id="error-estat_id"></div>
            </div>

        </div>

        <!-- BOTONES -->
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">

            <a
                href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-clients"
                class="btn btn-outline-secondary">
                ← Tornar enrere
            </a>

            <button
                type="submit"
                class="btn btn-primary"
                id="btnClient">
                Introduir dades
            </button>

        </div>

    </form>
</div>