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

                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['curriculum']; ?>/nou-link'" class="button btn-gran btn-secondari">Nou link</button>

                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['curriculum']; ?>/nova-habilitat'" class="button btn-gran btn-secondari">Nou habilitat</button>

                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['curriculum']; ?>/nova-experiencia'" class="button btn-gran btn-secondari">Nova experiència professional</button>

                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['curriculum']; ?>/nova-experiencia-i18n'" class="button btn-gran btn-secondari">Nova experiència professional i18n</button>

                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['curriculum']; ?>/nou-educacio'" class="button btn-gran btn-secondari">Nou educació</button>

                        <button onclick="window.location.href='<?php echo APP_INTRANET . $url['curriculum']; ?>/nou-educacio-i18n'" class="button btn-gran btn-secondari">Nou educació i18n</button>

                    </p>
                <?php endif; ?>
            </div>

            <div class="alert alert-success quadre">
                <ul class="llistat">
                    <li><a href="<?php echo APP_INTRANET . $url['curriculum']; ?>/perfil-cv">Veure perfil CV</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['curriculum']; ?>/perfil-cv-i18n">Veure perfil CV i18n</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['curriculum']; ?>/perfil-links">Veure links CV</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['curriculum']; ?>/perfil-habilitats">Veure habilitats CV</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['curriculum']; ?>/perfil-experiencies">Veure experiències professionals CV</a></li>
                    <li><a href="<?php echo APP_INTRANET . $url['curriculum']; ?>/perfil-educacio">Veure títols educatius CV</a></li>
                </ul>

                <ul class="llistat">
                    <li><a class="button btn-gran btn-secondari" href="https://elliot.cat/api/curriculum/pdf?id=1&locale=1" target="_blank">PDF Català</a></li>
                    <li><a class="button btn-gran btn-secondari" href="https://elliot.cat/api/curriculum/pdf?id=1&locale=3" target="_blank">PDF Castellano</a></li>
                    <li><a class="button btn-gran btn-secondari" href="https://elliot.cat/api/curriculum/pdf?id=1&locale=2" target="_blank">PDF English</a></li>
                    <li><a class="button btn-gran btn-secondari" href="https://elliot.cat/api/curriculum/pdf?id=1&locale=4" target="_blank">PDF Italiano</a></li>
                </ul>

                <p>db_curriculum_certificacions</p>
                <p>db_curriculum_certificacions_i18n</p>
                <p>db_curriculum_educacio</p>
                <p>db_curriculum_educacio_i18n</p>
                <p>db_curriculum_experiencia_professional</p>
                <p>db_curriculum_experiencia_professional_i18n</p>
                <p> * db_curriculum_habilitats</p>
                <p>db_curriculum_habilitats_experiencia</p>
                <p>db_curriculum_idiomes</p>
                <p> * db_curriculum_links</p>
                <p> * db_curriculum_perfil</p>
                <p> * db_curriculum_perfil_i18n</p>
                <p> db_curriculum_projectes</p>
                <p> db_curriculum_projectes_i18n</p>
                <p> db_curriculum_projectes_links</p>
            </div>

        </div>
    </main>
</div>