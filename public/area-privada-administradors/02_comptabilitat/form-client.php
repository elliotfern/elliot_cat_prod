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

            <!-- NOM -->
            <div class="col-md-4">
                <label for="nom" class="form-label">Nom *</label>
                <input
                    type="text"
                    class="form-control"
                    id="nom"
                    name="nom"
                    maxlength="255"
                    required>
                <div class="invalid-feedback" id="error-nom"></div>
            </div>

            <!-- COGNOMS -->
            <div class="col-md-4">
                <label for="cognoms" class="form-label">Cognoms</label>
                <input
                    type="text"
                    class="form-control"
                    id="cognoms"
                    name="cognoms"
                    maxlength="255">
                <div class="invalid-feedback" id="error-cognoms"></div>
            </div>


            <!-- EMPRESA -->
            <div class="col-md-4">
                <label for="empresa" class="form-label">Empresa</label>
                <input
                    type="text"
                    class="form-control"
                    id="empresa"
                    name="empresa"
                    maxlength="255">
                <div class="invalid-feedback" id="error-empresa"></div>
            </div>

            <!-- EMAIL -->
            <div class="col-md-4">
                <label for="email" class="form-label">Email *</label>
                <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    maxlength="255"
                    required>
                <div class="invalid-feedback" id="error-email"></div>
            </div>

            <!-- WEB -->
            <div class="col-md-4">
                <label for="web" class="form-label">Web</label>
                <input
                    type="url"
                    class="form-control"
                    id="web"
                    name="web"
                    maxlength="255"
                    placeholder="https://exemple.com">
                <div class="invalid-feedback" id="error-web"></div>
            </div>

            <!-- TELEFON -->
            <div class="col-md-4">
                <label for="telefon" class="form-label">Telèfon</label>
                <input
                    type="tel"
                    class="form-control"
                    id="telefon"
                    name="telefon"
                    maxlength="30">
                <div class="invalid-feedback" id="error-telefon"></div>
            </div>


            <!-- NIF -->
            <div class="col-md-4">
                <label for="nif" class="form-label">NIF</label>
                <input
                    type="text"
                    class="form-control"
                    id="nif"
                    name="nif"
                    maxlength="20">
                <div class="invalid-feedback" id="error-nif"></div>
            </div>

            <div class="col-md-8">
            </div>

            <hr>

            <!-- ADREÇA -->
            <div class="col-md-8">
                <label for="adreca" class="form-label">Adreça *</label>
                <input
                    type="text"
                    class="form-control"
                    id="adreca"
                    name="adreca"
                    maxlength="255"
                    required>
                <div class="invalid-feedback" id="error-adreca"></div>
            </div>

            <!-- CP -->
            <div class="col-md-4">
                <label for="cp" class="form-label">Codi Postal</label>
                <input
                    type="text"
                    class="form-control"
                    id="cp"
                    name="cp"
                    maxlength="10">
                <div class="invalid-feedback" id="error-cp"></div>
            </div>

            <!-- CIUTAT -->
            <div class="col-md-4">
                <label for="ciutat_id" class="form-label">Ciutat *</label>
                <select
                    class="form-select"
                    id="ciutat_id"
                    name="ciutat_id"
                    required></select>
                <div class="invalid-feedback" id="error-ciutat_id"></div>
            </div>

            <!-- PROVINCIA -->
            <div class="col-md-4">
                <label for="provincia_id" class="form-label">Província *</label>
                <select
                    class="form-select"
                    id="provincia_id"
                    name="provincia_id"
                    required></select>
                <div class="invalid-feedback" id="error-provincia_id"></div>
            </div>

            <!-- PAIS -->
            <div class="col-md-4">
                <label for="pais_id" class="form-label">País *</label>
                <select
                    class="form-select"
                    id="pais_id"
                    name="pais_id"
                    required></select>
                <div class="invalid-feedback" id="error-pais_id"></div>
            </div>

            <hr>

            <div id="inputCiutat" class="col-md-12"> </div>
            <div id="inputProvincia" class="col-md-12"> </div>
            <div id="inputPais" class="col-md-12"> </div>

            <hr>

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