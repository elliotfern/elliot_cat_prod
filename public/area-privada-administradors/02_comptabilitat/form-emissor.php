<?php

use App\Utils\Url;
?>

<div id="barraNavegacioContenidor"></div>

<h1>Gestió Comptabilitat i Clients</h1>
<h2>Formulari Emissor factures<span id="titolForm"></span></h2>

<div class="form">

    <!-- MESSAGES -->
    <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formEmissor" class="needs-validation" novalidate>

        <input type="hidden" id="id" name="id" />

        <div class="row g-3">

            <!-- NOM -->
            <div class="col-md-6">
                <label for="nom" class="form-label">Nom i cognoms *</label>
                <input type="text"
                    class="form-control"
                    id="nom"
                    name="nom"
                    required />
                <div class="invalid-feedback" id="error-nom"></div>
            </div>

            <!-- NIF -->
            <div class="col-md-6">
                <label for="nif" class="form-label">NIF *</label>
                <input type="text"
                    class="form-control"
                    id="nif"
                    name="nif"
                    required />
                <div class="invalid-feedback" id="error-nif"></div>
            </div>

            <!-- IVA -->
            <div class="col-md-6">
                <label for="numero_iva" class="form-label">Número IVA</label>
                <input type="text"
                    class="form-control"
                    id="numero_iva"
                    name="numero_iva" />
                <div class="invalid-feedback" id="error-numero_iva"></div>
            </div>

            <!-- PAÍS -->
            <div class="col-md-6">
                <label for="pais_id" class="form-label">País *</label>
                <select class="form-select"
                    id="pais_id"
                    name="pais_id"
                    required></select>
                <div class="invalid-feedback" id="error-pais_id"></div>
            </div>

            <!-- ADREÇA -->
            <div class="col-md-6">
                <label for="adreca" class="form-label">Adreça</label>
                <input type="text"
                    class="form-control"
                    id="adreca"
                    name="adreca" />
                <div class="invalid-feedback" id="error-adreca"></div>
            </div>

            <!-- TELÈFON -->
            <div class="col-md-6">
                <label for="telefon" class="form-label">Telèfon</label>
                <input type="tel"
                    class="form-control"
                    id="telefon"
                    name="telefon" />
                <div class="invalid-feedback" id="error-telefon"></div>
            </div>

            <!-- EMAIL -->
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email"
                    class="form-control"
                    id="email"
                    name="email" />
                <div class="invalid-feedback" id="error-email"></div>
            </div>

        </div>

        <!-- BOTONES -->
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">

            <a href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-emissors"
                class="btn btn-outline-secondary">
                ← Tornar enrere
            </a>

            <button type="submit"
                class="btn btn-primary"
                id="btnEmissor">
                Desar emissor
            </button>

        </div>

    </form>
</div>