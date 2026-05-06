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
            <label>Espai:</label>
            <select class="form-select" name="espai_id" id="espai_id" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Viatge:</label>
            <select class="form-select" name="viatge_id" id="viatge_id" value="">
            </select>
        </div>

        <div class="col-md-4">
            <label>Data visita:</label>
            <input class="form-control" type="date" name="dataVisita" id="dataVisita" value="">
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