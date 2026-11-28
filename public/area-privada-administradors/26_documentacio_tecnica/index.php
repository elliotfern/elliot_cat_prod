
<?php

use App\Utils\Routes;

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Documentació tècnica equip de treball</h1>

<?php if ($viewModel->isAdmin) : ?>

    <!-- Documentació tècnica -->
    <section class="mb-5" id="documentacio">

        <h2 class="h4 mb-3">Documentació tècnica</h2>

        <p>
            Documentació de la configuració del sistema i de l'entorn
            de treball, amb l'objectiu de poder reproduir-lo i restaurar-lo
            en un altre ordinador.
        </p>

        <div class="list-group">

            <div class="list-group-item">
                <strong><a href="<?php echo APP_GESTIO . $url['documentacio_tecnica'] . "/sistema-operatiu"?>">1. Sistema operatiu i configuració general</a></strong>
                <div class="small text-body-secondary">
                    Fedora, versions, kernel, arquitectura, hardware,
                    emmagatzematge i configuració general del sistema.
                </div>
            </div>

            <div class="list-group-item">
                <strong><a href="<?php echo APP_GESTIO . $url['documentacio_tecnica'] . "/entorn-grafic"?>">2. Entorn gràfic i aplicacions personals</a></strong>
                <div class="small text-body-secondary">
                    XFCE, gestor de sessions, configuració de l'escriptori,
                    aplicacions instal·lades i preferències personals.
                </div>
            </div>

            <div class="list-group-item">
                <strong><a href="<?php echo APP_GESTIO . $url['documentacio_tecnica'] . "/terminal"?>">3. Terminal i eines de consola</a></strong>
                <div class="small text-body-secondary">
                    Zsh, configuració del terminal, plugins, aliases,
                    variables d'entorn i eines de línia de comandes.
                </div>
            </div>

            <div class="list-group-item">
                <strong><a href="<?php echo APP_GESTIO . $url['documentacio_tecnica'] . "/xarxa-seguretat"?>">4. Xarxa i seguretat</a></strong>
                <div class="small text-body-secondary">
                    Xarxa, DNS, tallafoc, SSH, certificats, claus,
                    permisos i configuració de seguretat.
                </div>
            </div>

            <div class="list-group-item">
                <strong>5. Servidor web local</strong>
                <div class="small text-body-secondary">
                    Apache, virtual hosts, dominis locals, resolució DNS,
                    HTTPS i certificats SSL.
                </div>
            </div>

            <div class="list-group-item">
                <strong>6. PHP, Symfony i bases de dades</strong>
                <div class="small text-body-secondary">
                    Versions de PHP, extensions, PHP-FPM, Composer,
                    Symfony CLI, MariaDB i configuracions associades.
                </div>
            </div>

            <div class="list-group-item">
                <strong>7. JavaScript, TypeScript i eines frontend</strong>
                <div class="small text-body-secondary">
                    Node.js, npm, TypeScript, Webpack, SWC,
                    ESLint i altres eines de desenvolupament frontend.
                </div>
            </div>

            <div class="list-group-item">
                <strong>8. Git, GitHub i control de versions</strong>
                <div class="small text-body-secondary">
                    Git, configuració global, repositoris remots,
                    autenticació SSH, claus i integració amb GitHub.
                </div>
            </div>

            <div class="list-group-item">
                <strong>9. Editor i eines de desenvolupament</strong>
                <div class="small text-body-secondary">
                    Visual Studio Code, extensions, configuracions,
                    depuració i eines complementàries.
                </div>
            </div>

            <div class="list-group-item">
                <strong>10. Projectes i entorns locals</strong>
                <div class="small text-body-secondary">
                    Directoris, repositoris, dominis locals, bases de dades,
                    dependències i configuracions específiques dels projectes.
                </div>
            </div>

            <div class="list-group-item">
                <strong>11. Còpies de seguretat i configuracions</strong>
                <div class="small text-body-secondary">
                    Fitxers de configuració, inventaris de programari,
                    dades, claus, certificats i estratègia de còpies externes.
                </div>
            </div>

            <div class="list-group-item">
                <strong>12. Restauració i configuració d'un ordinador nou</strong>
                <div class="small text-body-secondary">
                    Procediment per instal·lar Fedora, recuperar les
                    configuracions, reinstal·lar les eines i reproduir
                    l'entorn de desenvolupament.
                </div>
            </div>

        </div>
    </section>

    <!-- Objectiu -->
    <section class="mb-4" id="objectiu">

        <h2 class="h4 mb-3">Objectiu d'aquesta documentació</h2>

        <p>
            Disposar d'una documentació tècnica clara i actualitzada
            de l'entorn informàtic, incloent-hi el sistema operatiu,
            les aplicacions personals i, especialment, totes les eines
            i configuracions necessàries per al desenvolupament de programari.
        </p>

        <p>
            Aquesta documentació ha de permetre reproduir l'entorn
            de treball en un ordinador nou, recuperant les configuracions,
            instal·lant les eines necessàries i restaurant els projectes
            i serveis locals.
        </p>

        <p>
            També ha d'identificar els fitxers de configuració que cal
            conservar, les dades que necessiten còpies de seguretat
            i els procediments necessaris per recuperar el sistema.
        </p>

    </section>

<?php endif; ?>
