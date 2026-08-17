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

    <form id="formMedicament" class="row g-3">

        <input type="hidden" id="id" name="id" value="">

        <div class="col-md-6">
            <label for="medicament" class="form-label">Medicament:</label>
            <input class="form-control" type="text" name="medicament" id="medicament" value="">
        </div>

        <div class="col-md-6">
            <label for="dosis" class="form-label">Dosis:</label>
            <input class="form-control" type="text" name="dosis" id="dosis" value="">
        </div>

        <div class="col-md-4">
            <label for="quantitat_defecte" class="form-label">Quantitat per defecte:</label>
            <input class="form-control" type="text" name="quantitat_defecte" id="quantitat_defecte" value="" placeholder="ex: 2 confezioni">
        </div>

        <div class="col-md-4">
            <label for="facultatiu_id" class="form-label">Facultatiu:</label>
            <select class="form-select" name="facultatiu_id" id="facultatiu_id"></select>
        </div>

        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="necessita_recepta" id="necessita_recepta" value="1" checked>
                <label class="form-check-label" for="necessita_recepta">
                    Necessita recepta
                </label>
            </div>
        </div>

        <div class="col-12 mt-4">
            <div class="d-flex justify-content-between">
                <a id="btnTornar" class="btn btn-secondary" href="#">
                    Llistat medicaments
                </a>

                <div class="d-flex gap-2">
                    <a
                        id="btnVeureFitxa"
                        class="btn btn-success d-none"
                        href="#">
                        Veure fitxa
                    </a>

                    <button id="btnMedicament" type="submit" class="btn btn-primary">
                        Afegir
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>