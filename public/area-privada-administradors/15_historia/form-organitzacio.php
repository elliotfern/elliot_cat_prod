<div id="barraNavegacioContenidor"></div>

<h1>Base de dades: Història Oberta</h1>

<div class="form">

    <div id="titolForm"></div>

    <div class="alert alert-success d-none" id="okMessage">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger d-none" id="errMessage">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formOrganitzacio" class="row g-3">

        <!-- Identificador -->
        <input type="hidden" id="id" name="id" value="">

        <!-- Dades generals -->
        <div class="col-12">
            <h5 class="border-bottom pb-2">Dades generals</h5>
        </div>

        <div class="col-md-8">
            <label for="organitzacio" class="form-label">Organització</label>
            <input
                class="form-control"
                type="text"
                name="organitzacio"
                id="organitzacio"
                maxlength="255"
                required>
        </div>

        <div class="col-md-4">
            <label for="sigla" class="form-label">Sigla</label>
            <input
                class="form-control"
                type="text"
                name="sigla"
                id="sigla"
                maxlength="10"
                required>
        </div>

        <div class="col-md-8">
            <label for="slug" class="form-label">Slug</label>
            <input
                class="form-control"
                type="text"
                name="slug"
                id="slug"
                required>
        </div>

        <div class="col-md-4">
            <label for="tipus" class="form-label">Tipus d'organització</label>
            <select class="form-select" name="tipus" id="tipus" required>
                <option value="">Selecciona un tipus</option>
                <option value="militar">Militar</option>
                <option value="politica">Política</option>
                <option value="social">Social</option>
                <option value="cultural">Cultural</option>
            </select>
        </div>

        <!-- Cronologia -->
        <div class="col-12 mt-4">
            <h5 class="border-bottom pb-2">Cronologia</h5>
        </div>

        <div class="col-md-6">
            <label for="any_fundacio" class="form-label">Any de fundació</label>
            <input
                class="form-control"
                type="number"
                name="any_fundacio"
                id="any_fundacio"
                min="0"
                max="2155"
                step="1"
                required>
        </div>

        <div class="col-md-6">
            <label for="any_dissolucio" class="form-label">Any de dissolució</label>
            <input
                class="form-control"
                type="number"
                name="any_dissolucio"
                id="any_dissolucio"
                min="0"
                max="2155"
                step="1"
                required>
        </div>

        <!-- Localització -->
        <div class="col-12 mt-4">
            <h5 class="border-bottom pb-2">Localització</h5>
        </div>

        <div class="col-md-4">
            <label for="pais_id" class="form-label">País</label>
            <select class="form-select" name="pais_id" id="pais_id" required>
                <option value="">Selecciona un país</option>
            </select>
        </div>

        <div class="col-md-4">
            <label for="ciutat_id" class="form-label">Ciutat</label>
            <select class="form-select" name="ciutat_id" id="ciutat_id" required>
                <option value="">Selecciona una ciutat</option>
            </select>
        </div>

        <div class="col-md-4">
            <label for="sub_etapa_id" class="form-label">Subetapa històrica</label>
            <select class="form-select" name="sub_etapa_id" id="sub_etapa_id" required>
                <option value="">Selecciona una subetapa</option>
            </select>
        </div>

        <!-- Classificació i recursos -->
        <div class="col-12 mt-4">
            <h5 class="border-bottom pb-2">Classificació i recursos</h5>
        </div>

        <div class="col-md-6">
            <label for="ideologia_id" class="form-label">Ideologia</label>
            <select class="form-select" name="ideologia_id" id="ideologia_id" required>
                <option value="">Selecciona una ideologia</option>
            </select>
        </div>

        <div class="col-md-6">
            <label for="imatge_id" class="form-label">Imatge</label>
            <select class="form-select" name="imatge_id" id="imatge_id" required>
                <option value="">Selecciona una imatge</option>
            </select>
        </div>

        <!-- Accions -->
        <div class="col-12 mt-4 d-flex justify-content-between">
            <button
                type="button"
                class="btn btn-secondary"
                onclick="window.history.back()">
                Tornar enrere
            </button>

            <button type="submit" id="btnOrg" class="btn btn-primary">
                Crea
            </button>
        </div>

    </form>
</div>