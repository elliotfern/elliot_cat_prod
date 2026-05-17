<?php

use App\Utils\Url;
?>

<div id="barraNavegacioContenidor"></div>

<h1>Gestió Comptabilitat i Clients</h1>
<h2>Formulari Emissor factures</h2>

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

    <form method="POST" action="" class="needs-validation" id="formEmissor" novalidate>

        <input type="hidden" id="id" name="id" />

        <div class="row g-3">

            <div class="col-md-6">
                <label for="nom" class="form-label fw-semibold">
                    Nom i cognoms *
                </label>

                <input
                    class="form-control"
                    type="text"
                    name="nom"
                    id="nom"
                    required />

                <div class="invalid-feedback">
                    Camp obligatori.
                </div>
            </div>

            <div class="col-md-6">
                <label for="nif" class="form-label fw-semibold">
                    NIF *
                </label>

                <input
                    class="form-control"
                    type="text"
                    name="nif"
                    id="nif"
                    required />

                <div class="invalid-feedback">
                    Camp obligatori.
                </div>
            </div>

            <div class="col-md-6">
                <label for="numero_iva" class="form-label fw-semibold">
                    Número IVA
                </label>

                <input
                    class="form-control"
                    type="text"
                    name="numero_iva"
                    id="numero_iva" />
            </div>

            <div class="col-md-6">
                <label for="pais" class="form-label fw-semibold">
                    País *
                </label>

                <select
                    class="form-select"
                    name="pais"
                    id="pais"
                    required>

                    <!-- Omplir amb llistat de països -->

                </select>

                <div class="invalid-feedback">
                    Selecciona un país.
                </div>
            </div>

            <div class="col-md-6">
                <label for="adreca" class="form-label fw-semibold">
                    Adreça
                </label>

                <input
                    class="form-control"
                    type="text"
                    name="adreca"
                    id="adreca" />
            </div>

            <div class="col-md-6">
                <label for="telefon" class="form-label fw-semibold">
                    Telèfon
                </label>

                <input
                    class="form-control"
                    type="text"
                    name="telefon"
                    id="telefon" />
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label fw-semibold">
                    Email
                </label>

                <input
                    class="form-control"
                    type="email"
                    name="email"
                    id="email" />
            </div>

        </div>

        <!-- Botones -->
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">

            <a
                href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-emissors"
                class="btn btn-outline-secondary">
                ← Tornar enrere
            </a>

            <button
                type="submit"
                class="btn btn-primary"
                id="btnEmissor">
                Desar emissor
            </button>

        </div>
    </form>
</div>