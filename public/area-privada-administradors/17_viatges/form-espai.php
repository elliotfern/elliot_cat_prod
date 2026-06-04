<div class="barraNavegacioContenidor"></div>

<div class="container-fluid form">

    <h2>Base de dades d’Espais</h2>
    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" class="row g-3" id="formEspai">

        <input type="hidden" name="id" id="id">

        <!-- NOM -->
        <div class="col-md-6">
            <label for="nom" class="form-label">Nom espai</label>
            <input class="form-control" type="text" name="nom" id="nom">
        </div>

        <!-- SLUG -->
        <div class="col-md-6">
            <label for="slug" class="form-label">Slug</label>
            <input class="form-control" type="text" name="slug" id="slug">
        </div>

        <!-- ANY FUNDACIÓ -->
        <div class="col-md-4">
            <label for="any_fundacio" class="form-label">Any fundació</label>
            <input class="form-control" type="text" name="any_fundacio" id="any_fundacio">
        </div>

        <!-- WEB -->
        <div class="col-md-4">
            <label for="web" class="form-label">Web</label>
            <input class="form-control" type="text" name="web" id="web">
        </div>

        <!-- TIPUS -->
        <div class="col-md-4">
            <label for="tipus_id" class="form-label">Tipus d’espai</label>
            <select class="form-select" name="tipus_id" id="tipus_id"></select>
        </div>

        <!-- CIUTAT -->
        <div class="col-md-4">
            <label for="ciutat_id" class="form-label">Ciutat</label>
            <select class="form-select" name="ciutat_id" id="ciutat_id"></select>
        </div>

        <!-- IMATGE -->
        <div class="col-md-4">
            <label for="img_id" class="form-label">Imatge</label>
            <select class="form-select" name="img_id" id="img_id"></select>
        </div>

        <!-- COORDENADES -->
        <div class="col-md-4">
            <label for="coordinades_latitud" class="form-label">Latitud</label>
            <input class="form-control" type="text" name="coordinades_latitud" id="coordinades_latitud">
        </div>

        <div class="col-md-4">
            <label for="coordinades_longitud" class="form-label">Longitud</label>
            <input class="form-control" type="text" name="coordinades_longitud" id="coordinades_longitud">
        </div>

        <!-- DESCRIPCIÓ -->
        <div class="col-12">
            <label for="descripcio" class="form-label">Descripció</label>
            <textarea class="form-control" id="descripcio" name="descripcio" rows="6"></textarea>
        </div>

        <!-- BOTONES -->
        <div class="col-12">
            <div class="d-flex justify-content-between mt-3">

                <button type="button" class="btn btn-secondary" onclick="history.back()">
                    ← Tornar enrere
                </button>

                <button type="submit" id="btnEspai" class="btn btn-primary">
                    Desa dades
                </button>

            </div>
        </div>

    </form>
</div>