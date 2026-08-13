<div id="barraNavegacioContenidor"></div>

<div class="form">
    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none">
        <div id="okText"></div>
    </div>
    <div class="alert alert-danger" id="errMessage" style="display:none">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formSubTema" class="row g-3">

        <input type="hidden" id="id" name="id" value="">

        <div class="col-md-4">
            <label for="tema_id" class="form-label">Tema</label>
            <select class="form-select" id="tema_id" name="tema_id">
            </select>
        </div>

        <div class="col-md-4">
            <label>Nom sub-tema:</label>
            <input class="form-control" type="text" name="sub_tema" id="sub_tema" value="">
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

                    <button id="btnSubTema" type="submit" class="btn btn-primary">
                        Afegir
                    </button>
                </div>
            </div>
        </div>

    </form>

</div>