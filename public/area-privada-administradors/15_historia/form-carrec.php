<div id="barraNavegacioContenidor"></div>

<h1 class="mb-4">Base de dades: Història Oberta</h1>

<div class="form">

    <div id="titolForm"></div>

    <div class="alert alert-success d-none" id="okMessage">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger d-none" id="errMessage">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formCarrec" class="row g-3">

        <input type="hidden" id="id" name="id" value="">

        <!-- Persona -->
        <div class="col-md-6">
            <label for="persona_id" class="form-label">Persona:</label>
            <select
                class="form-select"
                name="persona_id"
                id="persona_id"
                required>
                <option value="">Selecciona una persona</option>
            </select>
        </div>

        <!-- Càrrec -->
        <div class="col-md-6">
            <label for="carrecNom" class="form-label">Càrrec:</label>
            <input
                class="form-control"
                type="text"
                name="carrecNom"
                id="carrecNom"
                maxlength="155"
                required>
        </div>

        <!-- Any inici -->
        <div class="col-md-3">
            <label for="carrecInici" class="form-label">Any d'inici:</label>
            <input
                class="form-control"
                type="number"
                name="carrecInici"
                id="carrecInici"
                step="1">
        </div>

        <!-- Any fi -->
        <div class="col-md-3">
            <label for="carrecFi" class="form-label">Any de finalització:</label>
            <input
                class="form-control"
                type="number"
                name="carrecFi"
                id="carrecFi"
                step="1">
        </div>

        <!-- Organització -->
        <div class="col-md-6">
            <label for="organitzacio_id" class="form-label">
                Organització vinculada:
            </label>
            <select
                class="form-select"
                name="organitzacio_id"
                id="organitzacio_id">
                <option value="">Sense organització vinculada</option>
            </select>
        </div>

        <!-- Botons -->
        <div class="col-12 mt-4">
            <div class="d-flex justify-content-between align-items-center">

                <a
                    href="#"
                    onclick="window.history.back(); return false;"
                    class="btn btn-secondary">
                    Tornar enrere
                </a>

                <button
                    type="submit"
                    id="btnCarrec"
                    class="btn btn-primary">
                    Modifica càrrec
                </button>

            </div>
        </div>

    </form>

</div>