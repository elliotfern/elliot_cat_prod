<?php

// Configuración por defecto para rutas que requieren sesión, sin header_footer, con header_menu_footer
$defaultProtectedConfig = [
    'needs_session' => true,
    'header_footer' => false,
    'header_menu_footer' => true, // SIEMPRE TRUE
    'apiSenseHTML' => false,
];

// Helper único para TODAS las rutas
function route(string $viewPath): array
{
    global $defaultProtectedConfig;

    return array_merge($defaultProtectedConfig, [
        'view' => $viewPath,
    ]);
}

// Función que verifica si el usuario es Admin
function checkIfAdmin(): void
{
    if (!isUserAdmin()) {
        header('Location: /entrada');
        exit;
    }
}

$routes = [

    // 01. Homepage
    APP_GESTIO => route(APP_INTRANET_DIR . APP_HOMEPAGE_DIR . '/admin.php'),
    APP_GESTIO . '/admin' => route(APP_INTRANET_DIR . APP_HOMEPAGE_DIR . '/admin.php'),

    // 02. Comptabilitat
    APP_GESTIO . $url['comptabilitat'] => route(APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'index.php'),

    // 02.1 Clients
    APP_GESTIO . $url['comptabilitat'] . '/llistat-clients' => route(APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'llistat-clients.php'),
    APP_GESTIO . $url['comptabilitat'] . '/nou-client' => route(APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'form-client.php'),
    APP_GESTIO . $url['comptabilitat'] . '/modifica-client/{id}' => route(APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'form-client.php'),
    APP_GESTIO . $url['comptabilitat'] . '/facturacio-clients' => route(APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'llistat-factures-clients.php'),
    APP_GESTIO . $url['comptabilitat'] . '/nova-factura' => route(APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'form-factura-client.php'),
    APP_GESTIO . $url['comptabilitat'] . '/modifica-factura/{id}' => route(APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'form-factura-client.php'),
    APP_GESTIO . $url['comptabilitat'] . '/fitxa-factura-client/{id}' => route(APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'detalls-factura-client.php'),
    APP_GESTIO . $url['comptabilitat'] . '/nou-producte-factura' => route(APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'form-factura-producte.php'),
    APP_GESTIO . $url['comptabilitat'] . '/modifica-producte-factura/{id}' => route(APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'form-factura-producte.php'),

    // 04. Base de dades persones
    APP_GESTIO . $url['persones'] => route(APP_INTRANET_DIR . APP_PERSONES_DIR . 'index.php'),
    APP_GESTIO . $url['persones'] . '/llistat-persones' => route(APP_INTRANET_DIR . APP_PERSONES_DIR . 'index.php'),
    APP_GESTIO . $url['persones'] . '/fitxa-persona/{slug}' => route(APP_INTRANET_DIR . APP_PERSONES_DIR . 'fitxa-persona.php'),
    APP_GESTIO . $url['persones'] . '/modifica-persona/{slug}' => route(APP_INTRANET_DIR . APP_PERSONES_DIR . 'form-operacions-persona.php'),
    APP_GESTIO . $url['persones'] . '/nova-persona' => route(APP_INTRANET_DIR . APP_PERSONES_DIR . 'form-operacions-persona.php'),

    // 05. Programacio
    APP_GESTIO . $url['programacio'] => route(APP_INTRANET_DIR . APP_PROGRAMACIO_DIR . 'index.php'),
    APP_GESTIO . $url['programacio'] . '/daw' => route(APP_INTRANET_DIR . APP_PROGRAMACIO_DIR . 'daw.php'),
    APP_GESTIO . $url['programacio'] . '/links' => route(APP_INTRANET_DIR . APP_PROGRAMACIO_DIR . 'links.php'),
    APP_GESTIO . $url['programacio'] . '/links/{id}' => route(APP_INTRANET_DIR . APP_PROGRAMACIO_DIR . 'links-detail.php'),

    // 06. Gestor projectes
    APP_GESTIO . $url['projectes'] => route(APP_INTRANET_DIR . APP_PROJECTES_DIR . 'index.php'),
    APP_GESTIO . $url['projectes'] . '/nou-projecte' => route(APP_INTRANET_DIR . APP_PROJECTES_DIR . 'form-projecte.php'),
    APP_GESTIO . $url['projectes'] . '/modifica-projecte/{id}' => route(APP_INTRANET_DIR . APP_PROJECTES_DIR . 'form-projecte.php'),
    APP_GESTIO . $url['projectes'] . '/nova-tasca' => route(APP_INTRANET_DIR . APP_PROJECTES_DIR . 'form-tasca.php'),
    APP_GESTIO . $url['projectes'] . '/modifica-tasca/{id}' => route(APP_INTRANET_DIR . APP_PROJECTES_DIR . 'form-tasca.php'),

    // 07. Agenda contactes
    APP_GESTIO . $url['contactes'] => route(APP_INTRANET_DIR . APP_CONTACTES_DIR . 'index.php'),

    // 08. Biblioteca
    APP_GESTIO . $url['biblioteca'] => route(APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'index.php'),
    APP_GESTIO . $url['biblioteca'] . '/llistat-llibres' => route(APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'vista-llistat-llibres.php'),
    APP_GESTIO . $url['biblioteca'] . '/llistat-autors' => route(APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'vista-llistat-autors.php'),
    APP_GESTIO . $url['biblioteca'] . '/fitxa-llibre/{slug}' => route(APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'vista-llibre.php'),
    APP_GESTIO . $url['biblioteca'] . '/fitxa-autor/{slug}' => route(APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'vista-autor.php'),
    APP_GESTIO . $url['biblioteca'] . '/fitxa-llibre-autors/{slug}' => route(APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'vista-llibre-autors.php'),
    APP_GESTIO . $url['biblioteca'] . '/llibre-autors-afegir/{slug}' => route(APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'form-llibre-autors.php'),
    APP_GESTIO . $url['biblioteca'] . '/modifica-llibre/{slug}' => route(APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'form-modifica-llibre.php'),
    APP_GESTIO . $url['biblioteca'] . '/nou-llibre' => route(APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'form-modifica-llibre.php'),

    // 09. Adreces interes
    APP_GESTIO . $url['adreces'] => route(APP_INTRANET_DIR . APP_ADRECES_DIR . 'index.php'),
    APP_GESTIO . $url['adreces'] . '/llistat-links' => route(APP_INTRANET_DIR . APP_ADRECES_DIR . 'llistat-links.php'),
    APP_GESTIO . $url['adreces'] . '/nou-link' => route(APP_INTRANET_DIR . APP_ADRECES_DIR . 'form-link.php'),
    APP_GESTIO . $url['adreces'] . '/modifica-link/{id}' => route(APP_INTRANET_DIR . APP_ADRECES_DIR . 'form-link.php'),
    APP_GESTIO . $url['adreces'] . '/llistat-subtemes' => route(APP_INTRANET_DIR . APP_ADRECES_DIR . 'llistat-subtemes.php'),
    APP_GESTIO . $url['adreces'] . '/llistat-subtema/{id}' => route(APP_INTRANET_DIR . APP_ADRECES_DIR . 'llistat-subtema-id.php'),
    APP_GESTIO . $url['adreces'] . '/llistat-temes' => route(APP_INTRANET_DIR . APP_ADRECES_DIR . 'llistat-temes.php'),
    APP_GESTIO . $url['adreces'] . '/llistat-tema/{id}' => route(APP_INTRANET_DIR . APP_ADRECES_DIR . 'llistat-tema-id.php'),

    // 10. Claus acces
    APP_GESTIO . $url['vault'] => route(APP_INTRANET_DIR . APP_CLAUS_DIR . 'index.php'),
    APP_GESTIO . $url['vault'] . '/nou-vault' => route(APP_INTRANET_DIR . APP_CLAUS_DIR . 'form-clau.php'),
    APP_GESTIO . $url['vault'] . '/modifica-vault/{id}' => route(APP_INTRANET_DIR . APP_CLAUS_DIR . 'form-clau.php'),

    // 11. Cinema i televisió
    APP_GESTIO . $url['cinema'] => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'index.php'),
    APP_GESTIO . $url['cinema'] . '/llistat-pelicules' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-llistat-pelicules.php'),
    APP_GESTIO . $url['cinema'] . '/llistat-series' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-llistat-series.php'),
    APP_GESTIO . $url['cinema'] . '/llistat-directors' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-llistat-directors.php'),
    APP_GESTIO . $url['cinema'] . '/llistat-actors' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-llistat-actors.php'),
    APP_GESTIO . $url['cinema'] . '/llistat-obres-teatre' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-llistat-teatre.php'),
    APP_GESTIO . $url['cinema'] . '/fitxa-pelicula/{slug}' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-pelicula.php'),
    APP_GESTIO . $url['cinema'] . '/fitxa-actor/{slug}' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-actor.php'),
    APP_GESTIO . $url['cinema'] . '/fitxa-director/{slug}' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-director.php'),
    APP_GESTIO . $url['cinema'] . '/fitxa-serie/{slug}' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-serie.php'),
    APP_GESTIO . $url['cinema'] . '/nova-pelicula' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'form-pelicula.php'),
    APP_GESTIO . $url['cinema'] . '/modifica-pelicula/{id}' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'form-pelicula.php'),
    APP_GESTIO . $url['cinema'] . '/nova-serie' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'form-serie.php'),
    APP_GESTIO . $url['cinema'] . '/modifica-serie/{id}' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'form-serie.php'),
    APP_GESTIO . $url['cinema'] . '/inserir-actor-pelicula/{slug}' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'form-actor-pelicula.php'),
    APP_GESTIO . $url['cinema'] . '/modifica-actor-pelicula/{slug}' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'form-actor-pelicula.php'),
    APP_GESTIO . $url['cinema'] . '/inserir-actor-serie/{slug}' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'form-actor-serie.php'),
    APP_GESTIO . $url['cinema'] . '/modifica-actor-serie/{slug}' => route(APP_INTRANET_DIR . APP_CINEMA_DIR . 'form-actor-serie.php'),

    // 12. Xarxes socials
    APP_GESTIO . $url['xarxes'] => route(APP_INTRANET_DIR . APP_XARXES_DIR . 'index.php'),
    APP_GESTIO . $url['xarxes'] . '/mastodon' => route(APP_INTRANET_DIR . APP_XARXES_DIR . 'lector-mastodon.php'),
    APP_GESTIO . $url['xarxes'] . '/publica' => route(APP_INTRANET_DIR . APP_XARXES_DIR . 'nou-post.php'),

    // 13. Blog
    APP_GESTIO . $url['blog'] => route(APP_INTRANET_DIR . APP_BLOG_DIR . 'index.php'),
    APP_GESTIO . $url['blog'] . '/article/{slug}' => route(APP_INTRANET_DIR . APP_BLOG_DIR . 'fitxa-article.php'),
    APP_GESTIO . $url['blog'] . '/nou-article' => route(APP_INTRANET_DIR . APP_BLOG_DIR . 'modifica-article.php'),
    APP_GESTIO . $url['blog'] . '/modifica-article/{id}' => route(APP_INTRANET_DIR . APP_BLOG_DIR . 'modifica-article.php'),

    // 14. Lector rss
    APP_GESTIO . $url['rss'] => route(APP_INTRANET_DIR . APP_RSS_DIR . 'index.php'),

    // 15. Història
    APP_GESTIO . $url['historia'] => route(APP_INTRANET_DIR . APP_HISTORIA_DIR . 'index.php'),
    APP_GESTIO . $url['historia'] . '/llistat-cursos' => route(APP_INTRANET_DIR . APP_HISTORIA_DIR . 'llistat-cursos.php'),
    APP_GESTIO . $url['historia'] . '/llistat-organitzacions' => route(APP_INTRANET_DIR . APP_HISTORIA_DIR . 'llistat-organitzacions.php'),
    APP_GESTIO . $url['historia'] . '/llistat-esdeveniments' => route(APP_INTRANET_DIR . APP_HISTORIA_DIR . 'llistat-esdeveniments.php'),
    APP_GESTIO . $url['historia'] . '/fitxa-persona/{slug}' => route(APP_INTRANET_DIR . APP_HISTORIA_DIR . 'fitxa-persona.php'),
    APP_GESTIO . $url['historia'] . '/fitxa-politic/{slug}' => route(APP_INTRANET_DIR . APP_HISTORIA_DIR . 'fitxa-politic.php'),
    APP_GESTIO . $url['historia'] . '/fitxa-esdeveniment/{slug}' => route(APP_INTRANET_DIR . APP_HISTORIA_DIR . 'fitxa-esdeveniment.php'),
    APP_GESTIO . $url['historia'] . '/fitxa-organitzacio/{slug}' => route(APP_INTRANET_DIR . APP_HISTORIA_DIR . 'fitxa-organitzacio.php'),
    APP_GESTIO . $url['historia'] . '/nou-esdeveniment' => route(APP_INTRANET_DIR . APP_HISTORIA_DIR . 'form-esdeveniment.php'),
    APP_GESTIO . $url['historia'] . '/modifica-esdeveniment/{slug}' => route(APP_INTRANET_DIR . APP_HISTORIA_DIR . 'form-esdeveniment.php'),
    APP_GESTIO . $url['historia'] . '/modifica-esdeveniment-persona/{slug}' => route(APP_INTRANET_DIR . APP_HISTORIA_DIR . 'form-esdeveniment-persona.php'),
    APP_GESTIO . $url['historia'] . '/modifica-esdeveniment-organitzacio/{slug}' => route(APP_INTRANET_DIR . APP_HISTORIA_DIR . 'form-esdeveniment-organitzacio.php'),
    APP_GESTIO . $url['historia'] . '/nou-persona-carrec/{slug}' => route(APP_INTRANET_DIR . APP_HISTORIA_DIR . 'form-persona-carrec.php'),
    APP_GESTIO . $url['historia'] . '/modifica-persona-carrec/{id}' => route(APP_INTRANET_DIR . APP_HISTORIA_DIR . 'form-persona-carrec.php'),
    APP_GESTIO . $url['historia'] . '/modifica-organitzacio/{slug}' => route(APP_INTRANET_DIR . APP_HISTORIA_DIR . 'form-organitzacio.php'),
    APP_GESTIO . $url['historia'] . '/nova-organitzacio' => route(APP_INTRANET_DIR . APP_HISTORIA_DIR . 'form-organitzacio.php'),

    // 16. Auxiliars
    APP_GESTIO . $url['auxiliars'] => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'index.php'),
    APP_GESTIO . $url['auxiliars'] . '/llistat-imatges' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'llistat-imatges.php'),
    APP_GESTIO . $url['auxiliars'] . '/nova-imatge' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'imatges/form-inserir-imatge.php'),

    // Auxiliars > ciutats
    APP_GESTIO . $url['auxiliars'] . '/nova-ciutat' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'ciutats/form-ciutat.php'),
    APP_GESTIO . $url['auxiliars'] . '/modifica-ciutat/{id}' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'ciutats/form-ciutat.php'),
    APP_GESTIO . $url['auxiliars'] . '/llistat-ciutats' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'ciutats/llistat-ciutats.php'),

    // Auxiliars > paisos
    APP_GESTIO . $url['auxiliars'] . '/nou-pais' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'paisos/form-pais.php'),
    APP_GESTIO . $url['auxiliars'] . '/modifica-pais/{id}' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'paisos/form-pais.php'),
    APP_GESTIO . $url['auxiliars'] . '/llistat-paisos' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'paisos/llistat-paisos.php'),

    // Auxiliars > grups/professions persones
    APP_GESTIO . $url['auxiliars'] . '/llistat-grups' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'persones/llistat-grups.php'),
    APP_GESTIO . $url['auxiliars'] . '/nou-grup' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'persones/form-grup.php'),
    APP_GESTIO . $url['auxiliars'] . '/modifica-grup/{id}' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'persones/form-grup.php'),

    // Auxiliars : temes i subtemes
    APP_GESTIO . $url['auxiliars'] . '/llistat-subtemes' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'temes/llistat-subtemes.php'),
    APP_GESTIO . $url['auxiliars'] . '/nou-subtema' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'temes/form-subtema.php'),
    APP_GESTIO . $url['auxiliars'] . '/modifica-subtema/{id}' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'temes/form-subtema.php'),
    APP_GESTIO . $url['auxiliars'] . '/llistat-temes' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'temes/llistat-temes.php'),
    APP_GESTIO . $url['auxiliars'] . '/nou-tema' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'temes/form-tema.php'),
    APP_GESTIO . $url['auxiliars'] . '/modifica-tema/{id}' => route(APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'temes/form-tema.php'),

    // 17. Viatges
    APP_GESTIO . $url['viatges'] => route(APP_INTRANET_DIR . APP_VIATGES_DIR . 'index.php'),
    APP_GESTIO . $url['viatges'] . '/llistat-viatges' => route(APP_INTRANET_DIR . APP_VIATGES_DIR . 'llistat-viatges.php'),
    APP_GESTIO . $url['viatges'] . '/fitxa-viatge/{slug}' => route(APP_INTRANET_DIR . APP_VIATGES_DIR . 'fitxa-viatge.php'),
    APP_GESTIO . $url['viatges'] . '/fitxa-espai/{slug}' => route(APP_INTRANET_DIR . APP_VIATGES_DIR . 'fitxa-espai.php'),
    APP_GESTIO . $url['viatges'] . '/modifica-espai/{slug}' => route(APP_INTRANET_DIR . APP_VIATGES_DIR . 'form-espai.php'),
    APP_GESTIO . $url['viatges'] . '/nou-espai' => route(APP_INTRANET_DIR . APP_VIATGES_DIR . 'form-espai.php'),

    // Gestió usuaris
    APP_GESTIO . $url['usuaris'] => route(APP_INTRANET_DIR . APP_USUARIS_DIR . 'index.php'),
    APP_GESTIO . $url['usuaris'] . '/llistat-usuaris' => route(APP_INTRANET_DIR . APP_USUARIS_DIR . 'llistat-usuaris.php'),
    APP_GESTIO . $url['usuaris'] . '/nou-usuari' => route(APP_INTRANET_DIR . APP_USUARIS_DIR . 'form-usuaris.php'),
    APP_GESTIO . $url['usuaris'] . '/modifica-usuari/{id}' => route(APP_INTRANET_DIR . APP_USUARIS_DIR . 'form-usuaris.php'),

    // RADIO ONLINE
    APP_GESTIO . $url['radio'] => route(APP_INTRANET_DIR . APP_RADIO_DIR . 'index.php'),
    APP_GESTIO . $url['radio'] . '/catmusica' => route(APP_INTRANET_DIR . APP_RADIO_DIR . 'catmusica.php'),

    // CURRICULUM
    APP_GESTIO . $url['curriculum'] => route(APP_INTRANET_DIR . APP_CV_DIR . 'index.php'),
    APP_GESTIO . $url['curriculum'] . "/nou-perfil" => route(APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-perfil.php'),
    APP_GESTIO . $url['curriculum'] . "/modifica-perfil/{id}" => route(APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-perfil.php'),
    APP_GESTIO . $url['curriculum'] . "/perfil-cv" => route(APP_INTRANET_DIR . APP_CV_DIR . 'vista-perfil-cv.php'),
    APP_GESTIO . $url['curriculum'] . "/modifica-perfil-i18n/{locale}/{id}" => route(APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-perfil-i18n.php'),
    APP_GESTIO . $url['curriculum'] . "/nou-perfil-i18n" => route(APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-perfil-i18n.php'),
    APP_GESTIO . $url['curriculum'] . "/perfil-cv-i18n" => route(APP_INTRANET_DIR . APP_CV_DIR . 'vista-perfil-cv-i18n.php'),
    APP_GESTIO . $url['curriculum'] . "/nou-link" => route(APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-links.php'),
    APP_GESTIO . $url['curriculum'] . "/modifica-link/{id}" => route(APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-links.php'),
    APP_GESTIO . $url['curriculum'] . "/perfil-links" => route(APP_INTRANET_DIR . APP_CV_DIR . 'vista-perfil-linsk.php'),
    APP_GESTIO . $url['curriculum'] . "/nova-habilitat" => route(APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-habilitats.php'),
    APP_GESTIO . $url['curriculum'] . "/modifica-habilitat/{id}" => route(APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-habilitats.php'),
    APP_GESTIO . $url['curriculum'] . "/perfil-habilitats" => route(APP_INTRANET_DIR . APP_CV_DIR . 'vista-cv-habilitats.php'),
    APP_GESTIO . $url['curriculum'] . "/nova-experiencia" => route(APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-experiencia.php'),
    APP_GESTIO . $url['curriculum'] . "/modifica-experiencia/{id}" => route(APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-experiencia.php'),
    APP_GESTIO . $url['curriculum'] . "/perfil-experiencies" => route(APP_INTRANET_DIR . APP_CV_DIR . 'vista-perfil-experiencies.php'),
    APP_GESTIO . $url['curriculum'] . "/perfil-experiencia-professional/{id}" => route(APP_INTRANET_DIR . APP_CV_DIR . 'vista-cv-experiencia-professional-detalls.php'),
    APP_GESTIO . $url['curriculum'] . "/modifica-experiencia-i18n/{id}" => route(APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-experiencia-i18n.php'),
    APP_GESTIO . $url['curriculum'] . "/nova-experiencia-i18n" => route(APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-experiencia-i18n.php'),
    APP_GESTIO . $url['curriculum'] . "/nou-educacio" => route(APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-educacio.php'),
    APP_GESTIO . $url['curriculum'] . "/modifica-educacio/{id}" => route(APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-educacio.php'),
    APP_GESTIO . $url['curriculum'] . "/perfil-educacio" => route(APP_INTRANET_DIR . APP_CV_DIR . 'vista-perfil-educacio.php'),
    APP_GESTIO . $url['curriculum'] . "/nou-educacio-i18n" => route(APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-educacio-i18n.php'),
    APP_GESTIO . $url['curriculum'] . "/modifica-educacio-i18n/{id}" => route(APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-educacio-i18n.php'),
    APP_GESTIO . $url['curriculum'] . "/perfil-educacio-i18n/{id}" => route(APP_INTRANET_DIR . APP_CV_DIR . 'vista-perfil-educacio-i18n.php'),

    // Agenda esdeveniments
    APP_GESTIO . $url['agenda'] => route(APP_INTRANET_DIR . APP_AGENDA_DIR . 'index.php'),
    APP_GESTIO . $url['agenda'] . "/llistat-esdeveniments" => route(APP_INTRANET_DIR . APP_AGENDA_DIR . 'llistat-esdeveniments.php'),
    APP_GESTIO . $url['agenda'] . "/calendari-esdeveniments" => route(APP_INTRANET_DIR . APP_AGENDA_DIR . 'calendari-esdeveniments.php'),
    APP_GESTIO . $url['agenda'] . "/veure-esdeveniment/{id}" => route(APP_INTRANET_DIR . APP_AGENDA_DIR . 'veure-esdeveniment.php'),
    APP_GESTIO . $url['agenda'] . "/nou-esdeveniment" => route(APP_INTRANET_DIR . APP_AGENDA_DIR . 'form-esdeveniment.php'),
    APP_GESTIO . $url['agenda'] . "/modifica-esdeveniment/{id}" => route(APP_INTRANET_DIR . APP_AGENDA_DIR . 'form-esdeveniment.php'),
];

// Verificar si el usuario es admin antes de procesar las rutas privadas (admin)
if (isset($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], APP_GESTIO . '/gestio') === 0 || strpos($_SERVER['REQUEST_URI'], APP_GESTIO) === 0)) {
    checkIfAdmin();
}

return $routes;
