<div class="container-fluid form">

    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none">
        <div id="okText"></div>
    </div>
    <div class="alert alert-danger" id="errMessage" style="display:none">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formEducacioI18n" class="row g-3">

        <input type="hidden" id="id" name="id" value="">
        <input type="hidden" id="educacio_id" name="educacio_id" value="">

        <!-- Idioma -->
        <div class="col-md-4">
            <label for="locale" class="form-label">Idioma *</label>
            <select class="form-select" id="locale" name="locale" required>
            </select>
        </div>

        <!-- Grau -->
        <div class="col-md-4">
            <label for="grau" class="form-label">Grau / Títol *</label>
            <input class="form-control" type="text" name="grau" id="grau" required>
        </div>

        <!-- Notes -->
        <div class="col-complet">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="5"></textarea>
            <div class="form-text">Informació addicional (opcional).</div>
        </div>


        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left">
                </div>
                <div class="col-6 text-right derecha">
                    <button type="submit" class="btn btn-primary" id="btnEducacioi18n">Modificar dades</button>
                </div>
            </div>
        </div>
    </form>

</div>