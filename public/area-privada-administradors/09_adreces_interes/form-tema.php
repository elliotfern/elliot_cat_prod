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

    <form method="POST" action="" id="formTema" class="row g-3">

        <input type="hidden" id="id" name="id" value="">


        <div class="col-md-4">
            <label>Nom tema (Català):</label>
            <input class="form-control" type="text" name="tema_ca" id="tema_ca" value="">
        </div>

        <div class="col-md-4">
            <label>Nom tema (castellà):</label>
            <input class="form-control" type="text" name="tema_es" id="tema_es" value="">
        </div>

        <div class="col-md-4">
            <label>Nom tema (Anglès):</label>
            <input class="form-control" type="text" name="tema_en" id="tema_en" value="">
        </div>

        <div class="col-md-4">
            <label>Nom tema (Francès):</label>
            <input class="form-control" type="text" name="tema_fr" id="tema_fr" value="">
        </div>

        <div class="col-md-4">
            <label>Nom tema (Italià):</label>
            <input class="form-control" type="text" name="tema_it" id="tema_it" value="">
        </div>

        <div class="col-md-4">
        </div>

        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left">
                    <a href="#" onclick="window.history.back()" class="btn btn-secondary">Tornar enrere</a>
                </div>
                <div class="col-6 text-right derecha">

                    <button type="submit" class="btn btn-primary" id="btnTema">Modificar dades</button>

                </div>
            </div>
        </div>
    </form>

</div>