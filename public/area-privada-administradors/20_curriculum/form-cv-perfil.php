<div class="container-fluid form">

    <div id="titolForm"></div>

    <div class="alert alert-success" id="missatgeOk" style="display:none"></div>
    <div class="alert alert-danger" id="missatgeErr" style="display:none"></div>

    <form method="POST" action="" id="formCVPerfil" class="row g-3">

        <input type="hidden" id="id" name="id" value="">

        <div class="col-md-6">
            <label for="nom_complet" class="form-label">Nom complet *</label>
            <input class="form-control" type="text" name="nom_complet" id="nom_complet" required>
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label">Email *</label>
            <input class="form-control" type="email" name="email" id="email" required>
        </div>

        <div class="col-md-4">
            <label for="tel" class="form-label">Telèfon</label>
            <input class="form-control" type="text" name="tel" id="tel">
        </div>

        <div class="col-md-4">
            <label for="web" class="form-label">Pàgina web</label>
            <input class="form-control" type="url" name="web" id="web" placeholder="https://...">
        </div>

        <div class="col-md-4">
            <label for="localitzacio_ciutat" class="form-label">Ciutat</label>
            <select class="form-select" name="localitzacio_ciutat" id="localitzacio_ciutat">
                <!-- Si tens catàleg, carrega les opcions per API; placeholders: -->
            </select>
        </div>

        <!-- IMG PERFIL: usa el ID del media. Si tienes upload, deja ambos inputs: file + hidden con el ID -->
        <div class="col-md-4">
            <label for="img_perfil " class="form-label">Imatge perfil</label>
            <select class="form-select" name="img_perfil" id="img_perfil">
                <!-- Si tens catàleg, carrega les opcions per API; placeholders: -->
            </select>
        </div>

        <!-- DISPONIBILITAT: INT (catàleg). De moment, lista placeholder -->
        <div class="col-md-4">
            <label for="disponibilitat" class="form-label">Disponibilitat</label>
            <select class="form-select" name="disponibilitat" id="disponibilitat">
                <option value="">-- Sense especificar --</option>
                <!-- Si tens catàleg, carrega les opcions per API; placeholders: -->
                <option value="1">Immediata</option>
                <option value="2">Amb preavís</option>
                <option value="3">Freelance</option>
                <option value="4">Mitja jornada</option>
                <option value="5">Jornada completa</option>
            </select>
        </div>

        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="visibilitat" name="visibilitat" checked>
                <label class="form-check-label" for="visibilitat">Visible públicament</label>
            </div>
        </div>

        <div class="container" style="margin-top:25px">
            <div class="row">
                <div class="col-6 text-left">
                </div>
                <div class="col-6 text-right derecha">
                    <button type="submit" class="btn btn-primary" id="btnCVPerfil">Modificar dades</button>
                </div>
            </div>
        </div>
    </form>

</div>