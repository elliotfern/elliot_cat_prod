<div id="barraNavegacioContenidor"></div>

<div class="form">
    <div id="titolForm"></div>

    <div class="alert alert-success d-none" id="okMessage">
        <div id="okText"></div>
    </div>
    <div class="alert alert-danger d-none" id="errMessage">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formTema" class="row g-3">

        <input type="hidden" id="id" name="id" value="">


        <div class="col-md-4">
            <label>Nom tema:</label>
            <input class="form-control" type="text" name="tema" id="tema" value="">
        </div>

        <div class="col-md-4">
            <label>Ordre:</label>
            <input class="form-control" type="text" name="ordre" id="ordre" value="">
        </div>

        <div class="col-md-4">
        </div>

        <div class="col-12 mt-4">
            <div class="d-flex justify-content-between">
                <a id="btnTornar" class="btn btn-secondary" href="#">
                    Fitxa enllaç
                </a>

                <div class="d-flex gap-2">
                    <a
                        id="btnVeureFitxa"
                        class="btn btn-success d-none"
                        href="#">
                        Veure fitxa
                    </a>

                    <button id="btnTema" type="submit" class="btn btn-primary">
                        Afegir
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>