<div class="barraNavegacio">
</div>

<div class="container-fluid form">

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
            <select class="form-select" name="cat" id="cat" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Idioma:</label>
            <select class="form-select" name="lang" id="lang" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Tipus enllaç:</label>
            <select class="form-select" name="tipus" id="tipus" value="">
            </select>
        </div>

        <div class="col-md-4">
        </div>

        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left">
                    <a href="#" onclick="window.history.back()" class="btn btn-secondary">Tornar enrere</a>
                </div>
                <div class="col-6 text-right derecha">

                    <button type="submit" id="btnLink" class="btn btn-primary">Modifica enllaç</button>

                </div>
            </div>
        </div>
    </form>

</div>