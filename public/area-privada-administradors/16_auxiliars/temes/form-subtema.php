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

    <form method="POST" action="" id="formSubTema" class="row g-3">

        <input type="hidden" id="id" name="id" value="">

        <div class="col-md-4">
            <label for="tema_id" class="form-label">Tema</label>
            <select class="form-select" id="tema_id" name="tema_id">
            </select>
        </div>

        <div class="col-md-4">
            <label>Nom tema (Català):</label>
            <input class="form-control" type="text" name="sub_tema_ca" id="sub_tema_ca" value="">
        </div>

        <div class="col-md-4">
            <label>Nom tema (castellà):</label>
            <input class="form-control" type="text" name="sub_tema_es" id="sub_tema_es" value="">
        </div>

        <div class="col-md-4">
            <label>Nom tema (Anglès):</label>
            <input class="form-control" type="text" name="sub_tema_en" id="sub_tema_en" value="">
        </div>

        <div class="col-md-4">
            <label>Nom tema (Francès):</label>
            <input class="form-control" type="text" name="sub_tema_fr" id="sub_tema_fr" value="">
        </div>

        <div class="col-md-4">
            <label>Nom tema (Italià):</label>
            <input class="form-control" type="text" name="sub_tema_it" id="sub_tema_it" value="">
        </div>

        <div class="col-md-4">
        </div>

        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left">
                    <a href="#" onclick="window.history.back()" class="btn btn-secondary">Tornar enrere</a>
                </div>
                <div class="col-6 text-right derecha">

                    <button type="submit" class="btn btn-primary" id="btnSubTema">Modificar dades</button>

                </div>
            </div>
        </div>
    </form>

</div>