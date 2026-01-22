<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">
            <h2>Base de dades: Agenda</h2>
            <h3>Crear esdeveniment</h3>

            <div class="container-fluid form">

                <div id="titolForm"></div>

                <div class="alert alert-success" id="okMessage" style="display:none">
                    <div id="okText"></div>
                </div>
                <div class="alert alert-danger" id="errMessage" style="display:none">
                    <div id="errText"></div>
                </div>

                <form action="" id="formCrearEsdeveniment" class="row g-3">

                    <!-- Si luego haces modo edición, este hidden te sirve -->
                    <input type="hidden" id="id_esdeveniment" name="id_esdeveniment" value="">

                    <div class="col-md-12">
                        <label for="titol" class="form-label">Títol *</label>
                        <input class="form-control" type="text" name="titol" id="titol" maxlength="255" required>
                        <div class="invalid-feedback">El títol és obligatori.</div>
                    </div>

                    <div class="col-md-12">
                        <label for="descripcio" class="form-label">Descripció</label>
                        <textarea class="form-control" name="descripcio" id="descripcio" rows="3"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="tipus" class="form-label">Tipus *</label>
                        <select class="form-select" name="tipus" id="tipus" required>
                        </select>
                        <div class="invalid-feedback">Selecciona un tipus.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="estat" class="form-label">Estat</label>
                        <select class="form-select" name="estat" id="estat">
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label for="lloc" class="form-label">Lloc</label>
                        <input class="form-control" type="text" name="lloc" id="lloc" maxlength="255">
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="tot_el_dia" name="tot_el_dia">
                            <label class="form-check-label" for="tot_el_dia">Tot el dia</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="data_inici" class="form-label">Data inici *</label>
                        <input class="form-control" type="datetime-local" name="data_inici" id="data_inici" required>
                        <div class="invalid-feedback">Indica una data d'inici vàlida.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="data_fi" class="form-label">Data fi *</label>
                        <input class="form-control" type="datetime-local" name="data_fi" id="data_fi" required>
                        <div class="invalid-feedback">Indica una data de fi vàlida.</div>
                    </div>


                    <!-- Botonera al estilo de tu app -->
                    <div class="container" style="margin-top:25px">
                        <div class="row">
                            <div class="col-6 text-left">
                                <a href="/agenda" class="btn btn-outline-secondary">Cancel·lar</a>
                            </div>
                            <div class="col-6 text-right derecha">
                                <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
                            </div>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </main>
</div>