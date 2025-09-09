<div class="container-fluid form">

    <div id="titolForm"></div>

    <div class="alert alert-success" id="missatgeOk" style="display:none"></div>
    <div class="alert alert-danger" id="missatgeErr" style="display:none"></div>


    <form method="POST" action="" id="formCVPerfilI18n" class="row g-3">

        <!-- PK opcional (para ediciones). Para INSERT puedes dejarlo vacío u omitirlo -->
        <input type="hidden" id="id" name="id" value="">

        <!-- Perfil asociado (tu perfil suele ser 1) -->
        <div class="col-md-4">
            <label for="perfil_id" class="form-label">Perfil ID</label>
            <select class="form-select" id="perfil_id" name="perfil_id" required>
            </select>
        </div>

        <!-- Idioma / locale (INT en tu tabla). Ajusta los valores si tu mapping es distinto -->
        <div class="col-md-4">
            <label for="locale" class="form-label">Idioma</label>
            <select class="form-select" id="locale" name="locale" required>
            </select>
        </div>

        <!-- Titular -->
        <div class="col-complet">
            <label for="titular" class="form-label">Titular</label>
            <input class="form-control" type="text" id="titular" name="titular" maxlength="200" value="">
            <div class="form-text">Fins a 200 caràcters.</div>
        </div>

        <!-- Sumari (textarea simple) -->
        <div class="col-complet">
            <label for="sumari" class="form-label">Resum / Sumari</label>
            <textarea class="form-control" id="sumari" name="sumari" rows="6"></textarea>
        </div>

        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left">
                </div>
                <div class="col-6 text-right derecha">
                    <button type="submit" class="btn btn-primary" id="btnCVPerfili18n">Modificar dades</button>
                </div>
            </div>
        </div>
    </form>

</div>