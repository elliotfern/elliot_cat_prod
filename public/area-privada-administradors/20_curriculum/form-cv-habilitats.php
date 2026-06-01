<div class="container-fluid form">

    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none">
        <div id="okText"></div>
    </div>
    <div class="alert alert-danger" id="errMessage" style="display:none">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formCVHabilitats" class="row g-3">

        <!-- PK (autoincrement). En crear: buit; en editar: valor -->
        <input type="hidden" id="id" name="id" value="">

        <!-- Nom de l'habilitat -->
        <div class="col-md-4">
            <label for="nom" class="form-label">Habilitat *</label>
            <input class="form-control" type="text" id="nom" name="nom" maxlength="100" required
                placeholder="Ex.: JavaScript, PHP, React, MySQL">
        </div>

        <!-- Imatge/icone associat (ID de media opcional) -->
        <div class="col-md-4">
            <label for="imatge_id" class="form-label">Imatge ID</label>
            <select class="form-select" name="imatge_id" id="imatge_id">
                <!-- Si tens catàleg, carrega les opcions per API; placeholders: -->
            </select>
        </div>

        <!-- Posició d'ordenació -->
        <div class="col-md-4">
            <label for="posicio" class="form-label">Posició</label>
            <input class="form-control" type="number" id="posicio" name="posicio" value="0" step="1">
        </div>

        <!-- Accions -->
        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left"></div>
                <div class="col-6 text-right derecha">
                    <button type="submit" id="btnHabilitat" class="btn btn-primary">Desa habilitat</button>
                </div>
            </div>
        </div>
    </form>

</div>