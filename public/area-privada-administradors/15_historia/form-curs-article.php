<div class="barraNavegacio">
</div>

<div class="form">

    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none">
        <div id="okText"></div>
    </div>
    <div class="alert alert-danger" id="errMessage" style="display:none">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formCursArticle" class="row g-3">

        <!-- PK (si edites) -->
        <input type="hidden" id="id" name="id" value="">

        <div class="col-md-3">
            <label for="curs" class="form-label">Curs:</label>
            <select class="form-select" name="curs" id="curs">
                <!-- Options via TypeScript -->
            </select>
        </div>

        <div class="col-md-3">
            <label for="ordre" class="form-label">Ordre:</label>
            <input class="form-control" type="number" min="1" step="1" name="ordre" id="ordre" value="">
        </div>

        <div class="col-md-3">
            <label for="ca" class="form-label">Article (CAT):</label>
            <select class="form-select" name="ca" id="ca">
                <!-- Options via TypeScript -->
            </select>
        </div>

        <div class="col-md-3">
            <label for="es" class="form-label">Article (ES):</label>
            <select class="form-select" name="es" id="es">
                <!-- Options via TypeScript -->
            </select>
        </div>

        <div class="col-md-3">
            <label for="en" class="form-label">Article (EN):</label>
            <select class="form-select" name="en" id="en">
                <!-- Options via TypeScript -->
            </select>
        </div>

        <div class="col-md-3">
            <label for="fr" class="form-label">Article (FR):</label>
            <select class="form-select" name="fr" id="fr">
                <!-- Options via TypeScript -->
            </select>
        </div>

        <div class="col-md-3">
            <label for="it" class="form-label">Article (IT):</label>
            <select class="form-select" name="it" id="it">
                <!-- Options via TypeScript -->
            </select>
        </div>

        <div class="col-md-6">
            <!-- espacio / futuras acciones -->
        </div>

        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left">
                    <a href="#" onclick="window.history.back(); return false;" class="btn btn-secondary">Tornar enrere</a>
                </div>
                <div class="col-6 text-right derecha">
                    <button type="submit" id="btnCursArticle" class="btn btn-primary">Desar</button>
                </div>
            </div>
        </div>

    </form>

</div>