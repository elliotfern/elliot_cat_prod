<div class="container">

    <div id="barraNavegacioContenidor"></div>

    <main>
        <div class="container contingut">
            <h1>Currículum</h1>

            <div id="isAdminButton" style="display: none;">
                <?php if (isUserAdmin()) : ?>
                    <p>
                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['curriculum']; ?>/nou-perfil'" class="button btn-gran btn-secondari">Nou perfil</button>

                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['curriculum']; ?>/nou-perfil-i18n'" class="button btn-gran btn-secondari">Nou perfil i18n</button>

                    </p>
                <?php endif; ?>
            </div>

            <div class="alert alert-success quadre">
                <ul class="llistat">
                    <li> <a href="<?php echo APP_INTRANET . $url['curriculum']; ?>/perfil-cv">Veure perfil CV</a></li>
                    <li> <a href="<?php echo APP_INTRANET . $url['curriculum']; ?>/perfil-cv-i18n">Veure perfil CV i18n</a></li>
                </ul>

                db_curriculum_certificacions
                db_curriculum_certificacions_i18n
                db_curriculum_educacio
                db_curriculum_educacio_i18n
                db_curriculum_experiencia_professional
                db_curriculum_experiencia_professional_i18n
                db_curriculum_habilitats
                db_curriculum_habilitats_experiencia
                db_curriculum_idiomes
                db_curriculum_links
                * db_curriculum_perfil
                * db_curriculum_perfil_i18n
                db_curriculum_projectes
                db_curriculum_projectes_i18n
                db_curriculum_projectes_links
            </div>

        </div>
    </main>
</div>