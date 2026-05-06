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

    <form method="POST" action="" class="row g-3" id="formViatgeEspai">

        <input type="hidden" name="id" id="id">

        <!-- ESPAI -->
        <div class="col-md-4">
            <label for="espai_id" class="form-label">Espai</label>
            <select class="form-select" name="espai_id" id="espai_id"></select>
        </div>

        <!-- VIATGE -->
        <div class="col-md-4">
            <label for="viatge_id" class="form-label">Viatge</label>
            <select class="form-select" name="viatge_id" id="viatge_id"></select>
        </div>

        <!-- DATA VISITA -->
        <div class="col-md-4">
            <label for="dataVisita" class="form-label">Data visita</label>
            <input class="form-control" type="date" name="dataVisita" id="dataVisita">
        </div>

        <!-- BOTONES -->
        <div class="col-12">
            <div class="d-flex justify-content-between mt-3">

                <button type="button" class="btn btn-secondary" onclick="history.back()">
                    ← Tornar enrere
                </button>

                <button type="submit" id="btnEspai" class="btn btn-primary">
                    Desa dades
                </button>

            </div>
        </div>

    </form>
</div>