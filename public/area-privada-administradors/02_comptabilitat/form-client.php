<?php

use App\Utils\Url;
?>

<div id="barraNavegacioContenidor"></div>

<h1>Gestió Comptabilitat i Clients</h1>
<h2>Formulari Client: <div id="titolForm"></div>
</h2>

<div class="form">
    <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formClient" class="needs-validation" novalidate>

        <input type="hidden" id="id" name="id" />

        <div class="row g-3">

            <!-- NOM -->
            <div class="col-md-4">
                <label for="clientNom" class="form-label">Nom *</label>
                <input type="text"
                    class="form-control"
                    id="clientNom"
                    name="clientNom"
                    required
                    maxlength="255" />
                <div class="invalid-feedback" id="error-clientNom"></div>
            </div>

            <!-- COGNOMS -->
            <div class="col-md-4">
                <label for="clientCognoms" class="form-label">Cognoms</label>
                <input type="text"
                    class="form-control"
                    id="clientCognoms"
                    name="clientCognoms"
                    maxlength="255" />
                <div class="invalid-feedback" id="error-clientCognoms"></div>
            </div>

            <!-- EMAIL -->
            <div class="col-md-4">
                <label for="clientEmail" class="form-label">Email *</label>
                <input type="email"
                    class="form-control"
                    id="clientEmail"
                    name="clientEmail"
                    maxlength="255" />
                <div class="invalid-feedback" id="error-clientEmail"></div>
            </div>

            <!-- WEB -->
            <div class="col-md-4">
                <label for="clientWeb" class="form-label">Web</label>
                <input type="url"
                    class="form-control"
                    id="clientWeb"
                    name="clientWeb"
                    maxlength="255"
                    placeholder="https://exemple.com" />
                <div class="invalid-feedback" id="error-clientWeb"></div>
            </div>

            <!-- NIF -->
            <div class="col-md-4">
                <label for="clientNIF" class="form-label">NIF</label>
                <input type="text"
                    class="form-control"
                    id="clientNIF"
                    name="clientNIF"
                    maxlength="20" />
                <div class="invalid-feedback" id="error-clientNIF"></div>
            </div>

            <!-- EMPRESA -->
            <div class="col-md-4">
                <label for="clientEmpresa" class="form-label">Empresa</label>
                <input type="text"
                    class="form-control"
                    id="clientEmpresa"
                    name="clientEmpresa"
                    maxlength="255" />
                <div class="invalid-feedback" id="error-clientEmpresa"></div>
            </div>

            <!-- ADREÇA -->
            <div class="col-md-4">
                <label for="clientAdreca" class="form-label">Adreça *</label>
                <input type="text"
                    class="form-control"
                    id="clientAdreca"
                    name="clientAdreca"
                    maxlength="255" />
                <div class="invalid-feedback" id="error-clientAdreca"></div>
            </div>

            <!-- CP -->
            <div class="col-md-4">
                <label for="clientCP" class="form-label">Codi Postal</label>
                <input type="text"
                    class="form-control"
                    id="clientCP"
                    name="clientCP"
                    maxlength="10" />
                <div class="invalid-feedback" id="error-clientCP"></div>
            </div>

            <!-- PAIS -->
            <div class="col-md-4">
                <label for="pais_id" class="form-label">País *</label>
                <select class="form-select" id="pais_id" name="pais_id"></select>
                <div class="invalid-feedback" id="error-pais_id"></div>
            </div>

            <!-- PROVINCIA -->
            <div class="col-md-4">
                <label for="provincia_id" class="form-label">Província *</label>
                <select class="form-select" id="provincia_id" name="provincia_id"></select>
                <div class="invalid-feedback" id="error-provincia_id"></div>
            </div>

            <!-- CIUTAT -->
            <div class="col-md-4">
                <label for="ciutat_id" class="form-label">Ciutat *</label>
                <select class="form-select" id="ciutat_id" name="ciutat_id"></select>
                <div class="invalid-feedback" id="error-ciutat_id"></div>
            </div>

            <!-- TELEFON -->
            <div class="col-md-4">
                <label for="clientTelefon" class="form-label">Telèfon</label>
                <input type="tel"
                    class="form-control"
                    id="clientTelefon"
                    name="clientTelefon"
                    maxlength="30" />
                <div class="invalid-feedback" id="error-clientTelefon"></div>
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

            <!-- REGISTRE -->
            <div class="col-md-4">
                <label for="clientRegistre" class="form-label">Data registre *</label>
                <input type="date"
                    class="form-control"
                    id="clientRegistre"
                    name="clientRegistre" />
                <div class="invalid-feedback" id="error-clientRegistre"></div>
            </div>

        </div>

        <!-- BOTONES -->
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">

            <a href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-clients"
                class="btn btn-outline-secondary">
                ← Tornar enrere
            </a>

            <button type="submit"
                class="btn btn-primary"
                id="btnClient">
                Introduir dades
            </button>

        </div>

    </form>
</div>