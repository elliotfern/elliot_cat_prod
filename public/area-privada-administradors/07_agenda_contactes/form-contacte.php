<?php

use App\Utils\Url;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<div class="form">

  <h1>Gestió Contactes</h1>
  <h3><span id="titolForm"></span></h3>

  <div class="alert alert-success d-none" id="okMessage" role="alert">
    <div id="okText"></div>
  </div>

  <div class="alert alert-danger d-none" id="errMessage" role="alert">
    <div id="errText"></div>
  </div>

  <form method="POST" action="" id="formContacte" class="needs-validation" novalidate>

    <input type="hidden" id="id" name="id">

    <div class="row g-3">

      <!-- TIPUS DE CONTACTE -->
      <div class="col-md-4">
        <label for="tipus_persona" class="form-label">Tipus de contacte *</label>
        <select
          class="form-select"
          id="tipus_persona"
          name="tipus_persona"
          required>
          <option value="">Selecciona un tipus</option>
          <option value="FAMILIA">Família</option>
          <option value="AMICS">Amics</option>
          <option value="EMPRESA">Empresa</option>
          <option value="ALTRES">Altres</option>
        </select>
        <div class="invalid-feedback" id="error-tipus_persona"></div>
      </div>

      <hr>

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

      <!-- EMAIL -->
      <div class="col-md-4">
        <label for="email" class="form-label">Email</label>
        <input
          type="email"
          class="form-control"
          id="email"
          name="email"
          maxlength="255">
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

      <!-- TELÈFON 1 -->
      <div class="col-md-4">
        <label for="tel_1" class="form-label">Telèfon 1</label>
        <input
          type="tel"
          class="form-control"
          id="tel_1"
          name="tel_1"
          maxlength="255">
        <div class="invalid-feedback" id="error-tel_1"></div>
      </div>

      <!-- TELÈFON 2 -->
      <div class="col-md-4">
        <label for="tel_2" class="form-label">Telèfon 2</label>
        <input
          type="tel"
          class="form-control"
          id="tel_2"
          name="tel_2"
          maxlength="255">
        <div class="invalid-feedback" id="error-tel_2"></div>
      </div>

      <hr>

      <!-- DATA NAIXEMENT -->
      <div class="col-md-4">
        <label for="data_naixement" class="form-label">Data de naixement</label>
        <input
          type="date"
          class="form-control"
          id="data_naixement"
          name="data_naixement">
        <div class="invalid-feedback" id="error-data_naixement"></div>
      </div>

      <hr>

      <!-- ADREÇA -->
      <div class="col-md-8">
        <label for="adreca" class="form-label">Adreça</label>
        <input
          type="text"
          class="form-control"
          id="adreca"
          name="adreca"
          maxlength="255">
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
          maxlength="20">
        <div class="invalid-feedback" id="error-cp"></div>
      </div>

      <!-- CIUTAT -->
      <div class="col-md-4">
        <label for="ciutat_id" class="form-label">Ciutat</label>
        <select
          class="form-select"
          id="ciutat_id"
          name="ciutat_id"></select>
        <div class="invalid-feedback" id="error-ciutat_id"></div>
      </div>

      <!-- PROVÍNCIA -->
      <div class="col-md-4">
        <label for="provincia_id" class="form-label">Província</label>
        <select
          class="form-select"
          id="provincia_id"
          name="provincia_id"></select>
        <div class="invalid-feedback" id="error-provincia_id"></div>
      </div>

      <!-- PAÍS -->
      <div class="col-md-4">
        <label for="pais_id" class="form-label">País</label>
        <select
          class="form-select"
          id="pais_id"
          name="pais_id"></select>
        <div class="invalid-feedback" id="error-pais_id"></div>
      </div>

      <hr>

      <div id="inputCiutat" class="col-md-12"></div>
      <div id="inputProvincia" class="col-md-12"></div>
      <div id="inputPais" class="col-md-12"></div>

    </div>

    <hr>
    <div class="form-check form-switch">
      <input
        type="hidden"
        name="actiu"
        value="0">

      <input
        class="form-check-input"
        type="checkbox"
        id="actiu"
        name="actiu"
        value="1"
        checked>

      <label class="form-check-label" for="actiu">
        Contacte actiu
      </label>
    </div>

    <!-- BOTONES -->
    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">

      <a
        href="<?php echo Url::intranet('agenda-contactes'); ?>/llistat-contactes"
        class="btn btn-outline-secondary">
        ← Tornar enrere
      </a>

      <button
        type="submit"
        class="btn btn-primary"
        id="btnForm">
        Introduir dades
      </button>

    </div>

  </form>
</div>