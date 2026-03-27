<div class="barraNavegacio"></div>

<div class="container-fluid form">

    <h2>Gestió comptabilitat: Catàleg de productes</h2>
    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" class="row g-3" id="formProducte" data-success-redirect-template="/gestio/comptabilitat/fitxa-producte/{id}">

        <input type="hidden" id="id" name="id" />

        <div class="col-md-6">
            <label for="producte">Producte:</label>
            <input class="form-control" type="text" name="producte" id="producte" required />
        </div>

        <div class="col-md-6">
            <label for="unitat">Unitat:</label>
            <input class="form-control" type="text" name="unitat" id="unitat" placeholder="ex: hora, dia, projecte..." />
        </div>

        <div class="col-md-6">
            <label for="preu_recomanat">Preu recomanat (€):</label>
            <input class="form-control" type="number" step="0.01" name="preu_recomanat" id="preu_recomanat" />
        </div>

        <div class="col-md-6">
            <label for="actiu">Actiu:</label>
            <select class="form-select" name="actiu" id="actiu">
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </div>

        <div class="col-12">
            <label for="descripcio">Descripció:</label>
            <textarea class="form-control" name="descripcio" id="descripcio" rows="4"></textarea>
        </div>

        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left">
                    <!-- Espai per botons extra -->
                </div>
                <div class="col-6 text-right derecha">
                    <button type="submit" class="btn btn-primary" id="btnProducte">Desar Producte</button>
                </div>
            </div>
        </div>

    </form>
</div>