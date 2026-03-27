<div class="barraNavegacio"></div>

<div class="container-fluid form">

    <h2>Gestió comptabilitat: Emissors</h2>
    <div id="titolForm">Crear / Modificar Emissor</div>

    <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" class="row g-3" id="formEmissor">

        <input type="hidden" id="id" name="id" />

        <div class="col-md-6">
            <label for="nom">Nom:</label>
            <input class="form-control" type="text" name="nom" id="nom" required />
        </div>

        <div class="col-md-6">
            <label for="nif">NIF:</label>
            <input class="form-control" type="text" name="nif" id="nif" required />
        </div>

        <div class="col-md-6">
            <label for="numero_iva">Número IVA:</label>
            <input class="form-control" type="text" name="numero_iva" id="numero_iva" />
        </div>

        <div class="col-md-6">
            <label for="pais">País:</label>
            <select class="form-select" name="pais" id="pais" required>
                <!-- Omplir amb llistat de països -->
            </select>
        </div>

        <div class="col-md-6">
            <label for="adreca">Adreça:</label>
            <input class="form-control" type="text" name="adreca" id="adreca" />
        </div>

        <div class="col-md-6">
            <label for="telefon">Telèfon:</label>
            <input class="form-control" type="text" name="telefon" id="telefon" />
        </div>

        <div class="col-md-6">
            <label for="email">Email:</label>
            <input class="form-control" type="email" name="email" id="email" />
        </div>

        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left">
                    <!-- Espai per possibles botons addicionals -->
                </div>
                <div class="col-6 text-right derecha">
                    <button type="submit" class="btn btn-primary" id="btnEmissor">Desar Emissor</button>
                </div>
            </div>
        </div>

    </form>
</div>