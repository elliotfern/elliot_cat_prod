<div class="container-fluid form">
    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none">
        <div id="okText"></div>
    </div>
    <div class="alert alert-danger" id="errMessage" style="display:none">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formEducacio" class="row g-3">

        <input type="hidden" id="id" name="id" value="">

        <!-- Institució -->
        <div class="col-md-4">
            <label for="institucio" class="form-label">Institució *</label>
            <input class="form-control" type="text" name="institucio" id="institucio" required>
        </div>

        <!-- URL institució -->
        <div class="col-md-4">
            <label for="institucio_url" class="form-label">Web institució</label>
            <input class="form-control" type="url" name="institucio_url" id="institucio_url">
        </div>

        <!-- Localització -->
        <div class="col-md-4">
            <label for="institucio_localitzacio" class="form-label">Localització</label>
            <select class="form-select" id="institucio_localitzacio" name="institucio_localitzacio">
            </select>
        </div>

        <!-- Logo -->
        <div class="col-md-4">
            <label for="logo_id" class="form-label">Logo</label>
            <select class="form-select" id="logo_id" name="logo_id">
            </select>
        </div>

        <!-- Data inici -->
        <div class="col-md-4">
            <label for="data_inici" class="form-label">Data d'inici</label>
            <input class="form-control" type="date" name="data_inici" id="data_inici">
        </div>

        <!-- Data fi -->
        <div class="col-md-4">
            <label for="data_fi" class="form-label">Data de fi</label>
            <input class="form-control" type="date" name="data_fi" id="data_fi">
            <div class="form-text">Deixa en blanc si encara està en curs.</div>
        </div>

        <!-- Posició -->
        <div class="col-md-4">
            <label for="posicio" class="form-label">Posició</label>
            <input class="form-control" type="number" name="posicio" id="posicio" min="0">
        </div>

        <!-- Visible -->
        <div class="col-md-4">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" id="visible" name="visible" checked>
                <label class="form-check-label" for="visible">Visible</label>
            </div>
        </div>

        <!-- Botó submit -->
        <div class="container mt-3">
            <div class="row">
                <div class="col-6"></div>
                <div class="col-6 text-end">
                    <button type="submit" id="btnEducacio" class="btn btn-primary">Desar dades</button>
                </div>
            </div>
        </div>

    </form>
</div>