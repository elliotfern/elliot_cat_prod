<div id="barraNavegacioContenidor"></div>

<div class="form">
    <h2>Base de dades: Salut</h2>
    <h4 id="titolForm"></h4>

    <div id="okMessage" class="alert alert-success d-none">
        <span id="okText"></span>
    </div>

    <div id="errMessage" class="alert alert-danger d-none">
        <span id="errText"></span>
    </div>

    <form id="formFacultatiu" class="row g-3">

        <input type="hidden" id="id" name="id" value="">

        <div class="col-md-4">
            <label for="nom" class="form-label">Nom del facultatiu:</label>
            <input class="form-control" type="text" name="nom" id="nom" value="">
        </div>

        <div class="col-md-4">
            <label for="especialitat" class="form-label">Especialitat:</label>
            <input class="form-control" type="text" name="especialitat" id="especialitat" value="">
        </div>

        <div class="col-md-4">
            <label for="genere" class="form-label">Gènere (per a la concordança italiana):</label>
            <select class="form-select" name="genere" id="genere">
                <option value="f">Femení (della Dott.ssa.)</option>
                <option value="m">Masculí (del Dott.)</option>
            </select>
        </div>

        <div class="col-md-4">
            <label for="email" class="form-label">Email:</label>
            <input class="form-control" type="email" name="email" id="email" value="">
        </div>

        <div class="col-md-4">
            <label for="telefon" class="form-label">Telèfon:</label>
            <input class="form-control" type="tel" name="telefon" id="telefon" value="">
        </div>

        <div class="col-md-4">
            <label for="direccio" class="form-label">Adreça:</label>
            <input class="form-control" type="text" name="direccio" id="direccio" value="">
        </div>

        <div class="col-md-4">
            <label for="ciutat_id" class="form-label">Ciutat:</label>
            <select class="form-select" name="ciutat_id" id="ciutat_id"></select>
        </div>

        <div class="col-12 mt-4">
            <div class="d-flex justify-content-between">
                <a id="btnTornar" class="btn btn-secondary" href="#">
                    Llistat facultatius
                </a>

                <div class="d-flex gap-2">
                    <a
                        id="btnVeureFitxa"
                        class="btn btn-success d-none"
                        href="#">
                        Veure fitxa
                    </a>

                    <button id="btnFacultatiu" type="submit" class="btn btn-primary">
                        Afegir
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>