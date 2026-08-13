<div id="barraNavegacioContenidor"></div>

<div class="form">

    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none">
        <div id="okText"></div>
    </div>
    <div class="alert alert-danger" id="errMessage" style="display:none">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formLink" class="row g-3">

        <input type="hidden" id="id" name="id" value="">

        <div class="col-md-4">
            <label>Nom enllaç:</label>
            <input class="form-control" type="text" name="nom" id="nom" value="">
        </div>

        <div class="col-md-4">
            <label>Pàgina web:</label>
            <input class="form-control" type="text" name="web" id="web" value="">
        </div>

        <div class="col-md-4">
            <label>Categoria enllaç:</label>
            <select class="form-select" name="sub_tema_id" id="sub_tema_id" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Idioma:</label>
            <select class="form-select" name="idioma_id" id="idioma_id" value="">
            </select>
        </div>

        <div class=" col-md-4">
            <label>Tipus enllaç:</label>
            <select class="form-select" name="tipus" id="tipus" value="">
            </select>
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

                    <button id="btnLink" type="submit" class="btn btn-primary">
                        Afegir
                    </button>
                </div>
            </div>
        </div>

    </form>

</div>