<div class="barraNavegacio"></div>

<div class="container-fluid form">

    <h2>Gestió comptabilitat: Proveïdors</h2>
    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" class="row g-3" id="formProveidor" data-success-redirect-template="/gestio/comptabilitat/fitxa-proveidor/{id}">

        <input type="hidden" id="id" name="id" />

        <div class="col-md-6">
            <label for="nom">Nom:</label>
            <input class="form-control" type="text" name="nom" id="nom" required />
        </div>

        <div class="col-md-6">
            <label for="nif">NIF:</label>
            <input class="form-control" type="text" name="nif" id="nif" />
        </div>

        <div class="col-md-6">
            <label for="adreca">Adreça:</label>
            <input class="form-control" type="text" name="adreca" id="adreca" />
        </div>

        <div class="col-md-6">
            <label for="ciutat">Ciutat:</label>
            <input class="form-control" type="text" name="ciutat" id="ciutat" />
        </div>

        <div class="col-md-6">
            <label for="codi_postal">Codi Postal:</label>
            <input class="form-control" type="text" name="codi_postal" id="codi_postal" />
        </div>

        <div class="col-md-6">
            <label for="pais">País:</label>
            <input class="form-control" type="text" name="pais" id="pais" />
        </div>

        <div class="col-md-6">
            <label for="telefon">Telèfon:</label>
            <input class="form-control" type="text" name="telefon" id="telefon" />
        </div>

        <div class="col-md-6">
            <label for="email">Email:</label>
            <input class="form-control" type="email" name="email" id="email" />
        </div>

        <div class="col-md-6">
            <label for="web">Web:</label>
            <input class="form-control" type="text" name="web" id="web" />
        </div>

        <div class="col-md-6">
            <label for="contacte">Contacte:</label>
            <input class="form-control" type="text" name="contacte" id="contacte" />
        </div>

        <div class="col-12">
            <label for="notes">Notes:</label>
            <textarea class="form-control" name="notes" id="notes" rows="4"></textarea>
        </div>

        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left">
                    <!-- Espai per botons extra -->
                </div>
                <div class="col-6 text-right derecha">
                    <button type="submit" class="btn btn-primary" id="btnProveidor">Desar Proveïdor</button>
                </div>
            </div>
        </div>

    </form>
</div>