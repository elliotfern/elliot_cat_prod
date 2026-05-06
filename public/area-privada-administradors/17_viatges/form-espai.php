<div class="barraNavegacio"></div>

<div class="container-fluid form">
    <h2>Base de dades de Viatges</h2>
    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" class="row g-3" id="formViatge">

        <input type="hidden" name="id" id="id">

        <!-- VIATGE -->
        <div class="col-md-6">
            <label class="form-label" for="viatge">Nom viatge</label>
            <input class="form-control" type="text" name="viatge" id="viatge">
        </div>

        <!-- SLUG -->
        <div class="col-md-6">
            <label class="form-label" for="slug">Slug</label>
            <input class="form-control" type="text" name="slug" id="slug">
        </div>

        <!-- DESCRIPCIÓ -->
        <div class="col-12">
            <label class="form-label" for="descripcio">Descripció</label>
            <textarea class="form-control" name="descripcio" id="descripcio" rows="6"></textarea>
        </div>

        <!-- PAÍS -->
        <div class="col-md-4">
            <label class="form-label" for="pais_id">País</label>
            <select class="form-select" name="pais_id" id="pais_id"></select>
        </div>

        <!-- DATA INICI -->
        <div class="col-md-4">
            <label class="form-label" for="dataInici">Data inici</label>
            <input class="form-control" type="date" name="dataInici" id="dataInici">
        </div>

        <!-- DATA FI -->
        <div class="col-md-4">
            <label class="form-label" for="dataFi">Data fi</label>
            <input class="form-control" type="date" name="dataFi" id="dataFi">
        </div>

        <!-- IMATGE -->
        <div class="col-md-4">
            <label class="form-label" for="img_id">Imatge</label>
            <select class="form-select" name="img_id" id="img_id"></select>
        </div>

        <!-- BOTONS -->
        <div class="col-12">
            <div class="d-flex justify-content-between mt-3">

                <button
                    type="button"
                    class="btn btn-secondary"
                    onclick="history.back()">
                    ← Tornar enrere
                </button>

                <button type="submit" id="btnViatge" class="btn btn-primary">
                    Desa dades
                </button>

            </div>
        </div>

    </form>
</div>