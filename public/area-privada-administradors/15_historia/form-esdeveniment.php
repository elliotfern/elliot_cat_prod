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

    <form method="POST" action="" id="formEsdeveniment" class="row g-3">

        <!-- Identificador -->
        <input type="hidden" id="id" name="id" value="">

        <!-- Dades generals -->
        <div class="col-12">
            <h4>Dades de l'esdeveniment</h4>
        </div>

        <div class="col-md-6">
            <label for="esdeNom" class="form-label">Esdeveniment:</label>
            <input class="form-control" type="text" name="esdeNom" id="esdeNom" value="">
        </div>

        <div class="col-md-6">
            <label for="slug" class="form-label">Slug:</label>
            <input class="form-control" type="text" name="slug" id="slug" value="">
        </div>

        <!-- Data d'inici -->
        <div class="col-12">
            <h4>Data d'inici</h4>
        </div>

        <div class="col-md-4">
            <label for="esdeDataIDia" class="form-label">Dia:</label>
            <select class="form-select" name="esdeDataIDia" id="esdeDataIDia">
            </select>
        </div>

        <div class="col-md-4">
            <label for="esdeDataIMes" class="form-label">Mes:</label>
            <select class="form-select" name="esdeDataIMes" id="esdeDataIMes">
            </select>
        </div>

        <div class="col-md-4">
            <label for="esdeDataIAny" class="form-label">Any:</label>
            <input class="form-control" type="number" name="esdeDataIAny" id="esdeDataIAny">
        </div>

        <!-- Data de finalització -->
        <div class="col-12">
            <h4>Data de finalització</h4>
        </div>

        <div class="col-md-4">
            <label for="esdeDataFDia" class="form-label">Dia:</label>
            <select class="form-select" name="esdeDataFDia" id="esdeDataFDia">
            </select>
        </div>

        <div class="col-md-4">
            <label for="esdeDataFMes" class="form-label">Mes:</label>
            <select class="form-select" name="esdeDataFMes" id="esdeDataFMes">
            </select>
        </div>

        <div class="col-md-4">
            <label for="esdeDataFAny" class="form-label">Any:</label>
            <input class="form-control" type="number" name="esdeDataFAny" id="esdeDataFAny">
        </div>

        <!-- Ubicació i classificació -->
        <div class="col-12">
            <h4>Classificació i ubicació</h4>
        </div>

        <div class="col-md-4">
            <label for="sub_etapa_id" class="form-label">Subetapa històrica:</label>
            <select class="form-select" name="sub_etapa_id" id="sub_etapa_id">
            </select>
        </div>

        <div class="col-md-4">
            <label for="ciutat_id" class="form-label">Ciutat:</label>
            <select class="form-select" name="ciutat_id" id="ciutat_id">
            </select>
        </div>

        <div class="col-md-4">
            <label for="imatge_id" class="form-label">Imatge:</label>
            <select class="form-select" name="imatge_id" id="imatge_id">
            </select>
        </div>

        <!-- Ubicació i classificació -->
        <div class="col-12">
            <h4>Descripció esdeveniment</h4>
        </div>

        <!-- TRIX -->
        <div class="col-12">
            <label class="form-label fw-medium">Descripció</label>
            <input id="descripcio" name="descripcio" type="hidden">
            <trix-editor input="descripcio" class="form-control"></trix-editor>
        </div>


        <!-- Persones vinculades -->
        <div class="col-12">
            <hr>
            <h4>Persones vinculades a l'esdeveniment:</h4>
        </div>

        <div class="col-md-6">
            <label class="form-label">Persones:</label>

            <div id="personesContainer"></div>

            <button
                type="button"
                class="btn btn-sm btn-secondary mt-2"
                id="addPersonaBtn">
                + Afegir persona
            </button>
        </div>

        <!-- Organitzacions vinculades -->
        <div class="col-12">
            <hr>
            <h4>Organitzacions vinculades a l'esdeveniment:</h4>
        </div>

        <div class="col-md-6">
            <label class="form-label">Organitzacions:</label>

            <div id="organitzacionsContainer"></div>

            <button
                type="button"
                class="btn btn-sm btn-secondary mt-2"
                id="addOrganitzacioBtn">
                + Afegir organització
            </button>
        </div>

        <hr>

        <!-- Botons del formulari -->
        <div class="col-12 mt-4">
            <div class="d-flex justify-content-between flex-wrap gap-2">
                <a
                    href="#"
                    onclick="window.history.back()"
                    class="btn btn-secondary">
                    Tornar enrere
                </a>


                <button type="submit" id="btnEsde" class="btn btn-primary">
                    Modifica
                </button>

            </div>
        </div>

    </form>

</div>