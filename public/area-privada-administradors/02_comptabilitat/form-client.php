<?php

use App\Utils\Url;
?>

<div id="barraNavegacioContenidor"></div>

<h1>Gestió Comptabilitat i Clients</h1>
<h2>Formulari Clients</h2>

<div class="form">
    <h3>
        <div id="titolForm"></div>
    </h3>

    <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formClient" class="needs-validation" novalidate>

        <input type="hidden" id="id" name="id" />

        <div class="row g-3">

            <div class="col-md-4">
                <label for="clientNom" class="form-label">Nom *</label>
                <input
                    type="text"
                    class="form-control"
                    id="clientNom"
                    name="clientNom"
                    required
                    maxlength="255" />
                <div class="invalid-feedback">
                    Camp obligatori.
                </div>
            </div>

            <div class="col-md-4">
                <label for="clientCognoms" class="form-label">Cognoms</label>
                <input
                    type="text"
                    class="form-control"
                    id="clientCognoms"
                    name="clientCognoms"
                    maxlength="255" />
            </div>

            <div class="col-md-4">
                <label for="clientEmail" class="form-label">Email</label>
                <input
                    type="email"
                    class="form-control"
                    id="clientEmail"
                    name="clientEmail"
                    maxlength="255" />
                <div class="invalid-feedback">
                    Camp obligatori.
                </div>
            </div>

            <div class="col-md-4">
                <label for="clientWeb" class="form-label">Web</label>
                <input
                    type="url"
                    class="form-control"
                    id="clientWeb"
                    name="clientWeb"
                    maxlength="255"
                    placeholder="https://exemple.com" />
            </div>

            <div class="col-md-4">
                <label for="clientNIF" class="form-label">NIF</label>
                <input
                    type="text"
                    class="form-control"
                    id="clientNIF"
                    name="clientNIF"
                    maxlength="20"
                    pattern="^[A-Za-z0-9.\-]{1,20}$" />
            </div>

            <div class="col-md-4">
                <label for="clientEmpresa" class="form-label">Empresa</label>
                <input
                    type="text"
                    class="form-control"
                    id="clientEmpresa"
                    name="clientEmpresa"
                    maxlength="255" />
            </div>

            <div class="col-md-4">
                <label for="clientAdreca" class="form-label">Adreça</label>
                <input
                    type="text"
                    class="form-control"
                    id="clientAdreca"
                    name="clientAdreca"
                    maxlength="255" />
            </div>

            <div class="col-md-4">
                <label for="clientCP" class="form-label">Codi Postal</label>
                <input
                    type="text"
                    class="form-control"
                    id="clientCP"
                    name="clientCP"
                    maxlength="10"
                    pattern="^[A-Za-z0-9\- ]{3,10}$" />
            </div>

            <div class="col-md-4">
                <label for="pais_id" class="form-label">País</label>
                <select class="form-select" id="pais_id" name="pais_id"></select>
            </div>

            <div class="col-md-4">
                <label for="provincia_id" class="form-label">Província</label>
                <select class="form-select" id="provincia_id" name="provincia_id"></select>
            </div>

            <div class="col-md-4">
                <label for="ciutat_id" class="form-label">Ciutat</label>
                <select class="form-select" id="ciutat_id" name="ciutat_id"></select>
            </div>

            <div class="col-md-4">
                <label for="clientTelefon" class="form-label">Telèfon</label>
                <input
                    type="tel"
                    class="form-control"
                    id="clientTelefon"
                    name="clientTelefon"
                    maxlength="30"
                    pattern="^[0-9()+\-.\s]{6,30}$" />
            </div>

            <div class="col-md-4">
                <label for="clientStatus" class="form-label">Estat *</label>
                <select
                    class="form-select"
                    id="clientStatus"
                    name="clientStatus"
                    required>
                </select>
            </div>


        </div>

        <!-- Botones -->
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