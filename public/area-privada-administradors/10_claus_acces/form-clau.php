<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<div class="form">
    <h2>Base de dades: Claus d'accès</h2>
    <h4 id="titolForm"></h4>

    <div id="okMessage" class="alert alert-success" style="display:none">
        <span id="okText"></span>
    </div>

    <div id="errMessage" class="alert alert-danger" style="display:none">
        <span id="errText"></span>
    </div>

    <form id="formVault" class="row g-3">

        <input type="hidden" id="id" name="id" value="">

        <div class="col-md-4">
            <label>Servei:</label>
            <input class="form-control" type="text" name="servei" id="servei" value="">
        </div>

        <div class="col-md-4">
            <label>Usuari:</label>
            <input class="form-control" type="text" name="usuari" id="usuari" value="">
        </div>

        <div class="col-md-4">
            <label>Contrasenya:</label>
            <input class="form-control" type="password" name="password" id="password" value="">
        </div>

        <div class="col-md-4">
            <label>Clau 2Factor:</label>
            <input class="form-control" type="password" name="clau2f" id="clau2f" value="">
        </div>

        <div class="col-md-4">
            <label>Pàgina web:</label>
            <input class="form-control" type="text" name="web" id="web" value="">
        </div>

        <div class="col-md-4">
            <label>Tipus de servei:</label>
            <select class="form-select" name="tipus_id" id="tipus_id" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Notes:</label>
            <input class="form-control" type="text" name="notes" id="notes" value="">
        </div>

        <div class="col-12 mt-4">
            <div class="d-flex justify-content-between">
                <a id="btnTornar" class="btn btn-secondary" href="#">
                    Fitxa llibre
                </a>

                <div class="d-flex gap-2">
                    <a
                        id="btnVeureFitxa"
                        class="btn btn-success d-none"
                        href="#">
                        Veure fitxa
                    </a>

                    <button id="btnForm" type="submit" class="btn btn-primary">
                        Afegir
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>