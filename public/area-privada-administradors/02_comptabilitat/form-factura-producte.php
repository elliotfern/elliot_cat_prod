<div class="barraNavegacio"></div>

<div class="container-fluid form">

    <h2>Gestió comptabilitat i clients</h2>
    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" class="row g-3" id="formFacturaProducte" data-success-redirect-template="/gestio/base-dades-persones/fitxa-persona/{slug}">

        <div class="row g-3">
            <input type="hidden" id="id" name="id" />

            <div class="col-md-4">
                <label for="idUser">Factura:</label>
                <select class="form-select" name="factura_id" id="factura_id">
                </select>
            </div>

            <div class="col-md-4">
                <label for="idUser">Producte:</label>
                <select class="form-select" name="producte_id" id="producte_id">
                </select>
            </div>

            <div class="col-md-4">
            </div>

            <div class="col-md-4">
                <label for="facConcepte">Notes</label>
                <input class="form-control" type="text" name="notes" id="notes" />
            </div>

            <div class="col-md-4">
                <label for="facConcepte">Preu</label>
                <input class="form-control" type="text" name="preu" id="preu" />
            </div>

            <div class="col-md-4">
            </div>

            <div class="container" style="margin-top:25px">
                <div class="row">
                    <div class="col-6 text-left">

                    </div>
                    <div class="col-6 text-right derecha">
                        <button type="submit" class="btn btn-primary" id="btnFacturaProducte">Introduir dades</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>