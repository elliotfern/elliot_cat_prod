<div class="container-fluid form">

    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none">
        <div id="okText"></div>
    </div>
    <div class="alert alert-danger" id="errMessage" style="display:none">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formCVExperienciaI18n" class="row g-3">
        <!-- PK -->
        <input type="hidden" id="id" name="id" value="">

        <!-- Enlace a experiencia -->
        <div class="col-md-4">
            <label for="experiencia_id" class="form-label">Experiència professional</label>
            <select class="form-select" id="experiencia_id" name="experiencia_id" required>
            </select>
        </div>

        <!-- Idioma -->
        <div class="col-md-4">
            <label for="locale" class="form-label">Idioma</label>
            <select class="form-select" id="locale" name="locale" required>
            </select>
        </div>

        <!-- Rol / Títol -->
        <div class="col-md-4">
            <label for="rol_titol" class="form-label">Rol / Títol *</label>
            <input class="form-control" type="text" id="rol_titol" name="rol_titol" maxlength="190" required>
        </div>

        <!-- Sumari -->
        <div class="col-complet">
            <label for="sumari" class="form-label">Sumari</label>
            <textarea class="form-control" id="sumari" name="sumari" rows="4" placeholder="Breu descripció de les tasques, responsabilitats..."></textarea>
        </div>

        <!-- Fites (TRIX editor) -->
        <div class="col-complet">
            <label for="fites" class="form-label">Fites / Responsabilitats</label>

            <input id="fites" type="hidden" name="fites">
            <trix-editor input="fites" class="trix-content" style="min-height:200px"></trix-editor>
            <div class="form-text">Pots afegir llistes, negreta, enllaços...</div>
        </div>

        <!-- Botó -->
        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left"></div>
                <div class="col-6 text-right derecha">
                    <button type="submit" id="btnExperienciai18n" class="btn btn-primary">Desa detalls</button>
                </div>
            </div>
        </div>
    </form>

</div>