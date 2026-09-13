<div id="barraNavegacioContenidor"></div>

<div class="form">

    <div id="titolForm"></div>

    <div class="alert alert-success d-none" id="okMessage">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger d-none" id="errMessage">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" id="formCurs" class="row g-3">

        <input type="hidden" id="id" name="id" value="">

        <div class="col-md-8">
            <label for="curs" class="form-label">Curs:</label>
            <input
                class="form-control"
                type="text"
                name="curs"
                id="curs"
                value=""
                required>
        </div>

        <div class="col-md-4">
            <label for="ordre" class="form-label">Ordre:</label>
            <input
                class="form-control"
                type="number"
                name="ordre"
                id="ordre"
                value="">
        </div>

        <div class="col-12">
            <label for="resum" class="form-label">Resum:</label>
            <textarea
                class="form-control"
                name="resum"
                id="resum"
                rows="3"
                required></textarea>
        </div>

        <div class="col-12">
            <label for="descripcio" class="form-label">Descripció:</label>
            <textarea
                class="form-control"
                name="descripcio"
                id="descripcio"
                rows="8"></textarea>
        </div>

        <div class="col-md-8">
            <label for="slug" class="form-label">Slug:</label>
            <input
                class="form-control"
                type="text"
                name="slug"
                id="slug"
                value=""
                required>
        </div>

        <div class="col-md-4">
            <label for="img_id" class="form-label">Imatge:</label>
            <select
                class="form-select"
                name="img_id"
                id="img_id">
            </select>
        </div>

        <hr>

        <div class="col-12 mt-4">
            <div class="d-flex justify-content-between">

                <a
                    id="btnTornar"
                    class="btn btn-secondary"
                    href="#">
                    Llista cursos
                </a>

                <div class="d-flex gap-2">

                    <a
                        id="btnVeureFitxa"
                        class="btn btn-success d-none"
                        href="#">
                        Veure fitxa
                    </a>

                    <button
                        id="btnCurs"
                        type="submit"
                        class="btn btn-primary">
                        Afegir
                    </button>

                </div>
            </div>
        </div>

    </form>
</div>