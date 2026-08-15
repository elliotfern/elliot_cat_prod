<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<div class="form">
  <h2>Base de dades: Agenda de contactes</h2>
  <h4 id="titolForm"></h4>

  <div id="okMessage" class="alert alert-success" style="display:none">
    <span id="okText"></span>
  </div>

  <div id="errMessage" class="alert alert-danger" style="display:none">
    <span id="errText"></span>
  </div>

  <form id="formContacte" class="row g-3">

    <input type="hidden" id="id" name="id" value="">

    <div class="col-md-4">
      <label>Nom:</label>
      <input class="form-control" type="text" name="nom" id="nom" value="">
    </div>

    <div class="col-md-4">
      <label>Cognoms:</label>
      <input class="form-control" type="text" name="cognoms" id="cognoms" value="">
    </div>

    <div class="col-md-4">
      <label>Telèfon 1:</label>
      <input class="form-control" type="text" name="tel_1" id="tel_1" value="">
    </div>

    <div class="col-md-4">
      <label>Telèfon 2:</label>
      <input class="form-control" type="text" name="tel_2" id="tel_2" value="">
    </div>

    <div class="col-md-4">
      <label>Telèfon 3:</label>
      <input class="form-control" type="text" name="tel_3" id="tel_3" value="">
    </div>

    <div class="col-md-4">
      <label>Correu electrònic:</label>
      <input class="form-control" type="text" name="email" id="email" value="">
    </div>

    <div class="col-md-4">
      <label>Adreça:</label>
      <input class="form-control" type="text" name="adreca" id="adreca" value="">
    </div>

    <div class="col-md-4">
      <label>Data naixement:</label>
      <input class="form-control" type="text" name="data_naixement" id="data_naixement" value="">
    </div>

    <div class="col-md-4">
      <label>Pàgina web:</label>
      <input class="form-control" type="text" name="web" id="web" value="">
    </div>

    <div class="col-md-4">
      <label>Tipus de contacte:</label>
      <select class="form-select" name="tipus_id" id="tipus_id">
      </select>
    </div>

    <div class="col-md-4">
      <label>País:</label>
      <select class="form-select" name="pais_id" id="pais_id">
      </select>
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