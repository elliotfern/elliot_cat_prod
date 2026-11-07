<?php

use App\Utils\Routes;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Documentació tècnica equip de treball</h1>
<?php if ($viewModel->isAdmin) : ?>
    <!-- Documentació pendent -->
    <section class="mb-5" id="documentacio">
        <h2 class="h4 mb-3">Documentació tècnica</h2>

        <p>
            Els apartats següents es documentaran progressivament.
        </p>

        <div class="list-group">
            <div class="list-group-item">
                <strong>Configuració del sistema</strong>
                <div class="small text-body-secondary">
                    Usuaris, grups, permisos, variables d'entorn,
                    serveis i configuració del sistema.
                </div>
            </div>

            <div class="list-group-item">
                <strong>Xarxa i seguretat</strong>
                <div class="small text-body-secondary">
                    Xarxa, tallafoc, SSH, certificats i claus.
                </div>
            </div>

            <div class="list-group-item">
                <strong>Servidor web local</strong>
                <div class="small text-body-secondary">
                    Apache, virtual hosts, dominis locals i SSL.
                </div>
            </div>

            <div class="list-group-item">
                <strong>PHP i bases de dades</strong>
                <div class="small text-body-secondary">
                    Versions de PHP, extensions, Composer,
                    MariaDB i configuració.
                </div>
            </div>

            <div class="list-group-item">
                <strong>Entorn de desenvolupament</strong>
                <div class="small text-body-secondary">
                    Git, Node.js, npm, TypeScript, editors,
                    eines i configuracions.
                </div>
            </div>

            <div class="list-group-item">
                <strong>Projectes locals</strong>
                <div class="small text-body-secondary">
                    Directoris, dominis, repositoris,
                    bases de dades i dependències.
                </div>
            </div>

            <div class="list-group-item">
                <strong>Aplicacions i escriptori</strong>
                <div class="small text-body-secondary">
                    XFCE, aplicacions instal·lades i preferències.
                </div>
            </div>

            <div class="list-group-item">
                <strong>Còpies de seguretat</strong>
                <div class="small text-body-secondary">
                    Dades, configuracions, secrets i estratègia
                    d'emmagatzematge extern.
                </div>
            </div>

            <div class="list-group-item">
                <strong>Recuperació i reinstal·lació</strong>
                <div class="small text-body-secondary">
                    Procediment complet per reinstal·lar Fedora
                    i recuperar l'entorn de treball.
                </div>
            </div>
        </div>
    </section>

    <!-- Objectiu -->
    <section class="mb-4" id="objectiu">
        <h2 class="h4 mb-3">6. Objectiu d'aquesta documentació</h2>

        <p>
            Mantenir un inventari tècnic complet i actualitzat
            que permeti reinstal·lar Fedora des de zero i recuperar
            l'entorn de treball, les configuracions, els projectes,
            les bases de dades i els documents personals.
        </p>

        <p>
            La documentació s'haurà de complementar amb còpies
            de seguretat externes, verificades i protegides.
        </p>
    </section>

<?php endif; ?>