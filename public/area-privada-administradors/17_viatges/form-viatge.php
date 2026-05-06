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

        <input type="hidden" name="id" id="id" value="">

        <!-- VIATGE -->
        <div class="col-md-6">
            <label>Nom viatge:</label>
            <input class="form-control" type="text" name="viatge" id="viatge" value="">
        </div>

        <!-- SLUG -->
        <div class="col-md-6">
            <label>Slug:</label>
            <input class="form-control" type="text" name="slug" id="slug" value="">
        </div>

        <!-- DESCRIPCIÓ -->
        <div class="col-md-12">
            <label>Descripció:</label>
            <textarea class="form-control" name="descripcio" id="descripcio" rows="6"></textarea>
        </div>

        <!-- PAÍS -->
        <div class="col-md-4">
            <label>País:</label>
            <select class="form-select" name="pais_id" id="pais_id"></select>
        </div>

        <!-- DATES -->
        <div class="col-md-4">
            <label>Data inici:</label>
            <input class="form-control" type="date" name="dataInici" id="dataInici">
        </div>

        <div class="col-md-4">
            <label>Data fi:</label>
            <input class="form-control" type="date" name="dataFi" id="dataFi">
        </div>

        <!-- IMATGE -->
        <div class="col-md-4">
            <label>Imatge:</label>
            <select class="form-select" name="img_id" id="img_id"></select>
        </div>

        <!-- BOTONS -->
        <div class="container">
            <div class="row">
                <div class="col-6 text-start">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        onclick="history.back()">
                        ← Tornar enrere
                    </button>
                </div>

                <div class="col-6 text-end">
                    <button type="submit" id="btnViatge" class="btn btn-primary">
                        Desa dades
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>