<?php

use App\Utils\Url;
?>

<div id="barraNavegacioContenidor"></div>

<div class="container-fluid form">

    <h2>Gestió comptabilitat: Proveïdors</h2>
    <div id="titolForm"></div>

    <div class="alert alert-success d-none" id="okMessage" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger d-none" id="errMessage" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" class="row g-3" id="formProveidor">

        <input type="hidden" id="id" name="id" />

        <!-- CONTACTE ID -->
        <div class="col-md-4">
            <label for="contacte_id" class="form-label">Proveidor *</label>
            <select
                class="form-select"
                id="contacte_id"
                name="contacte_id"
                required></select>
            <div class="invalid-feedback" id="error-contacte_id"></div>
        </div>

        <!-- BOTONES -->
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">

            <a
                href="<?php echo Url::intranet('comptabilitat'); ?>/llistat-proveidors"
                class="btn btn-outline-secondary">
                ← Tornar enrere
            </a>

            <button
                type="submit"
                class="btn btn-primary"
                id="btnProveidor">
                Introduir dades
            </button>

        </div>

    </form>
</div>