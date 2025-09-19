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

    <form method="POST" action="" class="row g-3" id="formClient" data-success-redirect-template="/gestio/base-dades-persones/fitxa-persona/{slug}">

        <div class="row g-3">
            <input type="hidden" id="id" name="id" />

            <div class="col-md-4">
                <label for="clientNom" class="form-label">Nom *</label>
                <input type="text" class="form-control" id="clientNom" name="clientNom" required maxlength="255" />
                <div class="invalid-feedback">Camp obligatori.</div>
            </div>

            <div class="col-md-4">
                <label for="clientCognoms" class="form-label">Cognoms</label>
                <input type="text" class="form-control" id="clientCognoms" name="clientCognoms" maxlength="255" />
            </div>

            <div class="col-md-4">
                <label for="clientEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="clientEmail" name="clientEmail" maxlength="255" />
            </div>
            <div class="col-md-4">
                <label for="clientWeb" class="form-label">Web</label>
                <input type="url" class="form-control" id="clientWeb" name="clientWeb" maxlength="255" placeholder="https://exemple.com" />
            </div>

            <div class="col-md-4">
                <label for="clientNIF" class="form-label">NIF</label>
                <input type="text" class="form-control" id="clientNIF" name="clientNIF" maxlength="20"
                    pattern="^[A-Za-z0-9.\-]{1,20}$" />
            </div>
            <div class="col-md-4">
                <label for="clientEmpresa" class="form-label">Empresa</label>
                <input type="text" class="form-control" id="clientEmpresa" name="clientEmpresa" maxlength="255" />
            </div>

            <div class="col-md-4">
                <label for="clientAdreca" class="form-label">Adreça</label>
                <input type="text" class="form-control" id="clientAdreca" name="clientAdreca" maxlength="255" />
            </div>
            <div class="col-md-4">
                <label for="clientCP" class="form-label">Codi Postal</label>
                <input type="text" class="form-control" id="clientCP" name="clientCP" maxlength="10"
                    pattern="^[A-Za-z0-9\- ]{3,10}$" />
            </div>

            <!-- Ubicación (UUID v7 texto en selects) -->
            <div class="col-md-4">
                <label for="pais_id" class="form-label">País</label>
                <select class="form-select" id="pais_id" name="pais_id"></select>
            </div>
            <div class="col-md-4">
                <label for="provincia_id" class="form-label">Província</label>
                <select class="form-select" id="provincia_id" name="provincia_id"></select>
            </div>
            <div class="col-md-4">
                <label for="ciutat_id" class="form-label">Ciutat</label>
                <select class="form-select" id="ciutat_id" name="ciutat_id"></select>
            </div>

            <div class="col-md-4">
                <label for="clientTelefon" class="form-label">Telèfon</label>
                <input type="tel" class="form-control" id="clientTelefon" name="clientTelefon" maxlength="30"
                    pattern="^[0-9()+\-.\s]{6,30}$" />
            </div>

            <div class="col-md-4">
                <label for="clientStatus" class="form-label">Estat *</label>
                <select class="form-select" id="clientStatus" name="clientStatus" required>
                </select>
            </div>

            <div class="col-md-4">
                <label for="clientRegistre" class="form-label">Data de registre</label>
                <input type="date" class="form-control" id="clientRegistre" name="clientRegistre" />
            </div>


            <div class="container" style="margin-top:25px">
                <div class="row">
                    <div class="col-6 text-left">

                    </div>
                    <div class="col-6 text-right derecha">
                        <button type="submit" class="btn btn-primary" id="btnClient">Introduir dades</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>