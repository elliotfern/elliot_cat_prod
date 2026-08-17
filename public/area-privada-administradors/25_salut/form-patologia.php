<div id="barraNavegacioContenidor"></div>

<div class="form">
    <h2>Base de dades: Salut</h2>
    <h4 id="titolForm"></h4>

    <div id="okMessage" class="alert alert-success" style="display:none">
        <span id="okText"></span>
    </div>

    <div id="errMessage" class="alert alert-danger" style="display:none">
        <span id="errText"></span>
    </div>

    <form id="formPatologia" class="row g-3">

        <input type="hidden" id="id" name="id" value="">

        <div class="col-md-6">
            <label for="patologia" class="form-label">Patologia:</label>
            <input class="form-control" type="text" name="patologia" id="patologia" value="">
        </div>

        <div class="col-md-6">
            <label for="genere" class="form-label">Gènere (per a la concordança italiana):</label>
            <select class="form-select" name="genere" id="genere">
                <option value="f">Femení (della mia...)</option>
                <option value="m">Masculí (del mio...)</option>
            </select>
        </div>

        <hr>
        <h4>Medicaments associats:</h4>
        <div class="col-md-6">
            <label>Medicaments:</label>

            <div id="medicamentsContainer"></div>

            <button type="button" class="btn btn-sm btn-secondary mt-2" id="addMedicamentBtn">
                + Afegir medicament
            </button>
        </div>

        <hr>
        <div class="col-12 mt-4">
            <div class="d-flex justify-content-between">
                <a id="btnTornar" class="btn btn-secondary" href="#">
                    Llistat patologies
                </a>

                <div class="d-flex gap-2">
                    <a
                        id="btnVeureFitxa"
                        class="btn btn-success d-none"
                        href="#">
                        Veure fitxa
                    </a>

                    <button id="btnPatologia" type="submit" class="btn btn-primary">
                        Afegir
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>