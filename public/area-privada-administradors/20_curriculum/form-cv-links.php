<div class="container-fluid form">
    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none">
        <div id="okText"></div>
    </div>
    <div class="alert alert-danger" id="errMessage" style="display:none">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formCVLinks" class="row g-3" novalidate>
        <!-- PK (autoincrement). Buit en crear; omple'l en edició -->
        <input type="hidden" id="id" name="id" value="">

        <!-- Perfil asociado (tu perfil suele ser 1) -->
        <div class="col-md-4">
            <label for="perfil_id" class="form-label">Perfil ID</label>
            <select class="form-select" id="perfil_id" name="perfil_id" required>
            </select>
        </div>

        <!-- Etiqueta (GitHub, LinkedIn, Portfoli...) -->
        <div class="col-md-4">
            <label for="label" class="form-label">Etiqueta</label>
            <input class="form-control" type="text" id="label" name="label" maxlength="120">
        </div>

        <!-- URL -->
        <div class="col-md-4">
            <label for="url" class="form-label">URL *</label>
            <input class="form-control" type="url" id="url" name="url" required placeholder="https://...">
            <div class="form-text">Inclou <code>http://</code> o <code>https://</code>. Longitud màxima 512 caràcters.</div>
        </div>

        <!-- Ordre i visibilitat -->
        <div class="col-md-4">
            <label for="posicio" class="form-label">Posició</label>
            <input class="form-control" type="number" id="posicio" name="posicio" value="0" step="1">
            <div class="form-text">Ordenació manual (0,1,2...)</div>
        </div>

        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="visible" name="visible" value="1" checked>
                <label class="form-check-label" for="visible">Visible públicament</label>
            </div>
        </div>

        <!-- Accions -->
        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left"></div>
                <div class="col-6 text-right derecha">
                    <button type="submit" id="btnCVLink" class="btn btn-primary">Desa enllaç</button>
                </div>
            </div>
        </div>
    </form>
</div>