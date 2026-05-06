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

    <form method="POST" action="" class="row g-3" id="formEspai">

        <input type="hidden" name="id" id="id" value="">

        <div class="col-md-4">
            <label>Nom espai:</label>
            <input class="form-control" type="text" name="nom" id="nom" value="">
        </div>

        <div class="col-md-4">
            <label>Slug:</label>
            <input class="form-control" type="text" name="slug" id="slug" value="">
        </div>

        <div class="col-md-4">
            <label>Any fundació:</label>
            <input class="form-control" type="text" name="any_fundacio" id="any_fundacio" value="">
        </div>

        <div class="col-md-4">
            <label>Web:</label>
            <input class="form-control" type="text" name="eeb" id="web" value="">
        </div>

        <div class="col-md-4">
            <label>Coordinades: latitud:</label>
            <input class="form-control" type="text" name="coordinades_latitud" id="coordinades_latitud" value="">
        </div>

        <div class="col-md-4">
            <label>Coordinades: longitud:</label>
            <input class="form-control" type="text" name="coordinades_longitud" id="coordinades_longitud" value="">
        </div>

        <div class="col-md-4">
            <label>Tipus d'espai:</label>
            <select class="form-select" name="tipus_id" id="tipus_id" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Ciutat:</label>
            <select class="form-select" name="ciutat_id" id="ciutat_id" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Imatge:</label>
            <select class="form-select" name="img_id" id="img_id" value="">
            </select>
        </div>

        <div class="col-md-12">
            <label>Descripció:</label>
            <textarea id="descripcio" name="descripcio" rows="6" cols="50" value=""> </textarea>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-6 text-left">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        onclick="history.back()">
                        ← Tornar enrere
                    </button>
                </div>
                <div class="col-6 text-right derecha">
                    <button type="submit" id="btnEspai" class="btn btn-primary">
                        Desa dades
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>