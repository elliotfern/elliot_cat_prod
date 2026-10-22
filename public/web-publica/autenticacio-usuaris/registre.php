<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-6">

            <h1 class="h3 mb-4">Crear compte d'usuari</h1>

            <!-- Missatges de resposta -->
            <div
                class="alert alert-success d-none" id="okMessage">
                <div id="okText"></div>
            </div>

            <div
                class="alert alert-danger d-none" id="errMessage">
                <div id="errText"></div>
            </div>

            <form action="" method="post" id="formUsuari">

                <!-- Informació personal -->
                <div class="bg-body-tertiary border rounded p-3 p-md-4 mb-4">

                    <h2 class="h5 mb-4">Informació personal</h2>

                    <div class="row g-3">

                        <!-- Nom -->
                        <div class="col-12 col-md-6">
                            <label for="nom" class="form-label">
                                Nom
                            </label>

                            <input
                                type="text"
                                name="nom"
                                id="nom"
                                class="form-control"
                                autocomplete="given-name"
                                required>
                        </div>

                        <!-- Cognoms -->
                        <div class="col-12 col-md-6">
                            <label for="cognom" class="form-label">
                                Cognoms
                            </label>

                            <input
                                type="text"
                                name="cognom"
                                id="cognom"
                                class="form-control"
                                autocomplete="family-name"
                                required>
                        </div>

                    </div>
                </div>


                <!-- Correu electrònic -->
                <div class="mb-4">
                    <label for="email" class="form-label">
                        Correu electrònic
                    </label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        autocomplete="email"
                        required>

                    <div id="error-email" class="invalid-feedback">
                    </div>
                </div>


                <div class="mb-3">
                    <label for="password" class="form-label">Contrasenya</label>
                    <div class="input-group">
                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            autocomplete="new-password"
                            minlength="12"
                            required>
                        <button
                            class="btn btn-outline-secondary"
                            type="button"
                            data-toggle-password="password"
                            aria-label="Mostrar contrasenya"
                            aria-pressed="false">◉</button>
                    </div>
                    <div class="form-text">Mínim 12 caràcters.</div>
                </div>

                <div class="mb-3">
                    <label for="passwordConfirm" class="form-label">
                        Repeteix la contrasenya
                    </label>
                    <div class="input-group">
                        <input
                            type="password"
                            class="form-control"
                            id="passwordConfirm"
                            name="passwordConfirm"
                            autocomplete="new-password"
                            required
                            aria-describedby="passwordConfirmError">
                        <button
                            class="btn btn-outline-secondary"
                            type="button"
                            data-toggle-password="passwordConfirm"
                            aria-label="Mostrar contrasenya"
                            aria-pressed="false">◉</button>
                    </div>
                    <div
                        id="passwordConfirmError"
                        class="invalid-feedback"
                        aria-live="polite"></div>
                </div>


                <!-- Acceptació legal -->
                <div class="mb-3">
                    <div class="form-check">
                        <input
                            type="checkbox"
                            name="acceptaPrivacitat"
                            id="acceptaPrivacitat"
                            class="form-check-input"
                            value="1"
                            required>

                        <label
                            for="acceptaPrivacitat"
                            class="form-check-label">
                            Accepto l'<a
                                href="https://elliot.cat/avis-legal"
                                target="_blank"
                                rel="noopener noreferrer"
                                title="Consulta l'avís legal">avís legal</a>
                            i la
                            <a
                                href="https://elliot.cat/politica-privacitat"
                                target="_blank"
                                rel="noopener noreferrer"
                                title="Consulta la política de privadesa">política de privadesa</a>
                            d'Elliot.cat.
                        </label>
                    </div>
                </div>

                <!-- Informació sobre protecció de dades -->
                <div class="small text-body-secondary mb-4">
                    <p>
                        Les dades personals que ens proporcioneu seran
                        tractades per Elliot Fernandez, com a responsable
                        del tractament, amb la finalitat de gestionar el
                        vostre compte d'usuari i permetre-us accedir als
                        serveis d'Elliot.cat.
                    </p>

                    <p>
                        La base jurídica del tractament serà la gestió de
                        la vostra sol·licitud de registre i de la relació
                        establerta amb vosaltres. El fet de no proporcionar
                        les dades obligatòries pot impedir la creació del
                        compte.
                    </p>

                    <p>
                        Les dades no se cediran a tercers, excepte quan
                        existeixi una obligació legal o sigui necessari
                        per prestar els serveis, i no es preveuen
                        transferències internacionals de dades.
                    </p>

                    <p class="mb-0">
                        Podeu exercir els vostres drets d'accés,
                        rectificació, supressió, oposició, limitació i,
                        quan correspongui, portabilitat, així com presentar
                        una reclamació davant l'autoritat de control
                        competent. Trobareu informació addicional sobre
                        el tractament de les dades a la nostra
                        <a
                            href="https://elliot.cat/politica-de-privacitat"
                            target="_blank"
                            rel="noopener noreferrer">política de privadesa</a>.
                    </p>
                </div>

                <!-- Botó de registre -->
                <div class="d-grid d-sm-flex">
                    <button
                        type="submit"
                        name="registre"
                        id="btnRegistre"
                        class="btn btn-primary px-4">
                        Registrar-me
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>