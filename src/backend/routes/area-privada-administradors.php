<?php

// Configuración por defecto para rutas que requieren sesión, sin header_footer, con header_menu_footer
$defaultProtectedConfig = [
    'needs_session' => true,
    'header_footer' => false,
    'header_menu_footer' => false,
    'apiSenseHTML' => false,
    'menu_intranet' => true
];

// Función que verifica si el usuario es Admin
function checkIfAdmin()
{
    if (!isUserAdmin()) {
        // Si no es admin, redirigimos al login o a una página de acceso denegado
        header('Location: /entrada');
        exit;
    }
}

$routes = [

    // 01. Homepage
    APP_GESTIO => array_merge($defaultProtectedConfig, [
        'view' => APP_INTRANET_DIR . APP_HOMEPAGE_DIR . '/admin.php'
    ]),

    APP_GESTIO . '/admin' => array_merge($defaultProtectedConfig, [
        'view' => APP_INTRANET_DIR . APP_HOMEPAGE_DIR . '/admin.php'
    ]),

    // 02. Comptabilitat
    APP_GESTIO . $url['comptabilitat'] => [
        'view' => APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // 02.1 Clients
    APP_GESTIO . $url['comptabilitat'] . '/llistat-clients' => [
        'view' => APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'llistat-clients.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['comptabilitat'] . '/nou-client' => [
        'view' => APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'form-client.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['comptabilitat'] . '/modifica-client/{id}' => [
        'view' => APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'form-client.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['comptabilitat'] . '/facturacio-clients' => [
        'view' => APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'llistat-factures-clients.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['comptabilitat'] . '/nova-factura' => [
        'view' => APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'form-factura-client.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['comptabilitat'] . '/modifica-factura/{id}' => [
        'view' => APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'form-factura-client.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['comptabilitat'] . '/fitxa-factura-client/{id}' => [
        'view' => APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'detalls-factura-client.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['comptabilitat'] . '/nou-producte-factura' => [
        'view' => APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'form-factura-producte.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['comptabilitat'] . '/modifica-producte-factura/{id}' => [
        'view' => APP_INTRANET_DIR . APP_COMPTABILITAT_DIR . 'form-factura-producte.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // 04. Base de dades persones
    APP_GESTIO . $url['persones'] => [
        'view' =>  APP_INTRANET_DIR . APP_PERSONES_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['persones'] . '/llistat-persones' => [
        'view' =>  APP_INTRANET_DIR . APP_PERSONES_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['persones'] . '/fitxa-persona/{slug}' => [
        'view' =>  APP_INTRANET_DIR . APP_PERSONES_DIR . 'fitxa-persona.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],


    APP_GESTIO . $url['persones'] . '/modifica-persona/{slug}' => [
        'view' =>  APP_INTRANET_DIR . APP_PERSONES_DIR . 'form-operacions-persona.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['persones'] . '/nova-persona' => [
        'view' =>  APP_INTRANET_DIR . APP_PERSONES_DIR . 'form-operacions-persona.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // 05. Programacio
    APP_GESTIO . $url['programacio'] => [
        'view' => APP_INTRANET_DIR . APP_PROGRAMACIO_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['programacio'] . '/daw' => [
        'view' => APP_INTRANET_DIR . APP_PROGRAMACIO_DIR . 'daw.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['programacio'] . '/links' => [
        'view' => APP_INTRANET_DIR . APP_PROGRAMACIO_DIR . 'links.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['programacio'] . '/links/{id}' => [
        'view' => APP_INTRANET_DIR . APP_PROGRAMACIO_DIR . 'links-detail.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // 06. Gestor projectes
    APP_GESTIO . $url['projectes'] => [
        'view' => APP_INTRANET_DIR . APP_PROJECTES_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // 07. Agenda contactes
    APP_GESTIO . $url['contactes'] => [
        'view' => APP_INTRANET_DIR . APP_CONTACTES_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // 08. Biblioteca
    APP_GESTIO . $url['biblioteca'] => [
        'view' => APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['biblioteca'] . '/llistat-llibres' => [
        'view' => APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'vista-llistat-llibres.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['biblioteca'] . '/llistat-autors' => [
        'view' => APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'vista-llistat-autors.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['biblioteca'] . '/fitxa-llibre/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'vista-llibre.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['biblioteca'] . '/fitxa-autor/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'vista-autor.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['biblioteca'] . '/modifica-llibre/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'form-modifica-llibre.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['biblioteca'] . '/nou-llibre' => [
        'view' => APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'form-modifica-llibre.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // 09. Adreces interes
    APP_GESTIO . $url['adreces'] => [
        'view' => APP_INTRANET_DIR . APP_ADRECES_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['adreces'] . '/llistat-links' => [
        'view' => APP_INTRANET_DIR . APP_ADRECES_DIR . 'llistat-links.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['adreces'] . '/nou-link' => [
        'view' => APP_INTRANET_DIR . APP_ADRECES_DIR . 'form-link.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['adreces'] . '/modifica-link/{id}' => [
        'view' => APP_INTRANET_DIR . APP_ADRECES_DIR . 'form-link.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['adreces'] . '/llistat-subtemes' => [
        'view' => APP_INTRANET_DIR . APP_ADRECES_DIR . 'llistat-subtemes.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['adreces'] . '/llistat-subtema/{id}' => [
        'view' => APP_INTRANET_DIR . APP_ADRECES_DIR . 'llistat-subtema-id.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['adreces'] . '/nou-subtema' => [
        'view' => APP_INTRANET_DIR . APP_ADRECES_DIR . 'form-subtema.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['adreces'] . '/modifica-subtema/{id}' => [
        'view' => APP_INTRANET_DIR . APP_ADRECES_DIR . 'form-subtema.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['adreces'] . '/llistat-temes' => [
        'view' => APP_INTRANET_DIR . APP_ADRECES_DIR . 'llistat-temes.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['adreces'] . '/llistat-tema/{id}' => [
        'view' => APP_INTRANET_DIR . APP_ADRECES_DIR . 'llistat-tema-id.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['adreces'] . '/nou-tema' => [
        'view' => APP_INTRANET_DIR . APP_ADRECES_DIR . 'form-tema.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['adreces'] . '/modifica-tema/{id}' => [
        'view' => APP_INTRANET_DIR . APP_ADRECES_DIR . 'form-tema.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],



    // 10. Claus acces
    APP_GESTIO . $url['vault'] => [
        'view' => APP_INTRANET_DIR . APP_CLAUS_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['vault'] . '/nou-vault' => [
        'view' => APP_INTRANET_DIR . APP_CLAUS_DIR . 'form-clau.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['vault'] . '/modifica-vault/{id}' => [
        'view' => APP_INTRANET_DIR . APP_CLAUS_DIR . 'form-clau.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // 11. Cinema i televisió
    APP_GESTIO . $url['cinema'] => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] . '/llistat-pelicules' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-llistat-pelicules.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] . '/llistat-series' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-llistat-series.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] . '/llistat-directors' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-llistat-directors.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] . '/llistat-actors' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-llistat-actors.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] . '/llistat-obres-teatre' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-llistat-teatre.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] . '/fitxa-pelicula/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-pelicula.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] . '/fitxa-actor/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-actor.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] . '/fitxa-director/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-director.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] . '/fitxa-serie/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-serie.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] . '/nova-pelicula' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'form-pelicula.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] . '/modifica-pelicula/{id}' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'form-pelicula.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] . '/nova-serie' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'form-serie.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] .  '/modifica-serie/{id}' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'form-serie.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] . '/inserir-actor-pelicula/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'form-actor-pelicula.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] . '/modifica-actor-pelicula/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'form-actor-pelicula.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] . '/inserir-actor-serie/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'form-actor-serie.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['cinema'] . '/modifica-actor-serie/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'form-actor-serie.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // 12. Xarxes socials
    APP_GESTIO . $url['xarxes'] => [
        'view' => APP_INTRANET_DIR . APP_XARXES_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['xarxes'] . '/mastodon' => [
        'view' => APP_INTRANET_DIR . APP_XARXES_DIR . 'lector-mastodon.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['xarxes'] . '/publica' => [
        'view' => APP_INTRANET_DIR . APP_XARXES_DIR . 'nou-post.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // 13. Blog
    APP_GESTIO . $url['blog'] => [
        'view' => APP_INTRANET_DIR . APP_BLOG_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['blog'] . '/article/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_BLOG_DIR . 'fitxa-article.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['blog'] . '/nou-article' => [
        'view' => APP_INTRANET_DIR . APP_BLOG_DIR . 'modifica-article.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['blog'] . '/modifica-article/{id}' => [
        'view' => APP_INTRANET_DIR . APP_BLOG_DIR . 'modifica-article.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // 14. Lector rss
    APP_GESTIO . $url['rss'] => [
        'view' =>  APP_INTRANET_DIR . APP_RSS_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // 15. Història
    APP_GESTIO . $url['historia'] => [
        'view' => APP_INTRANET_DIR . APP_HISTORIA_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['historia'] . '/llistat-cursos' => [
        'view' => APP_INTRANET_DIR . APP_HISTORIA_DIR . 'llistat-cursos.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['historia'] . '/llistat-organitzacions' => [
        'view' => APP_INTRANET_DIR . APP_HISTORIA_DIR . 'llistat-organitzacions.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['historia'] . '/llistat-esdeveniments' => [
        'view' => APP_INTRANET_DIR . APP_HISTORIA_DIR . 'llistat-esdeveniments.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['historia'] . '/fitxa-persona/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_HISTORIA_DIR . 'fitxa-persona.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['historia'] . '/fitxa-politic/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_HISTORIA_DIR . 'fitxa-politic.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['historia'] . '/fitxa-esdeveniment/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_HISTORIA_DIR . 'fitxa-esdeveniment.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['historia'] . '/fitxa-organitzacio/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_HISTORIA_DIR . 'fitxa-organitzacio.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['historia'] . '/nou-esdeveniment' => [
        'view' => APP_INTRANET_DIR . APP_HISTORIA_DIR . 'form-esdeveniment.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['historia'] . '/modifica-esdeveniment/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_HISTORIA_DIR . 'form-esdeveniment.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['historia'] . '/modifica-esdeveniment-persona/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_HISTORIA_DIR . 'form-esdeveniment-persona.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['historia'] . '/modifica-esdeveniment-organitzacio/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_HISTORIA_DIR . 'form-esdeveniment-organitzacio.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['historia'] . '/nou-persona-carrec/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_HISTORIA_DIR . 'form-persona-carrec.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['historia'] . '/modifica-persona-carrec/{id}' => [
        'view' => APP_INTRANET_DIR . APP_HISTORIA_DIR . 'form-persona-carrec.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['historia'] . '/modifica-organitzacio/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_HISTORIA_DIR . 'form-organitzacio.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['historia'] . '/nova-organitzacio' => [
        'view' => APP_INTRANET_DIR . APP_HISTORIA_DIR . 'form-organitzacio.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // 16. Auxiliars
    APP_GESTIO . $url['auxiliars'] => [
        'view' => APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['auxiliars'] . '/llistat-imatges' => [
        'view' => APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'llistat-imatges.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['auxiliars'] . '/nova-imatge' => [
        'view' => APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'imatges/form-inserir-imatge.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // Auxiliars > ciutats
    APP_GESTIO . $url['auxiliars'] . '/nova-ciutat' => [
        'view' => APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'ciutats/form-ciutat.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['auxiliars'] . '/modifica-ciutat/{id}' => [
        'view' => APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'ciutats/form-ciutat.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['auxiliars'] . '/llistat-ciutats' => [
        'view' => APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'ciutats/llistat-ciutats.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // Auxiliars > paisos
    APP_GESTIO . $url['auxiliars'] . '/nou-pais' => [
        'view' => APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'paisos/form-pais.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['auxiliars'] . '/modifica-pais/{id}' => [
        'view' => APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'paisos/form-pais.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['auxiliars'] . '/llistat-paisos' => [
        'view' => APP_INTRANET_DIR . APP_AUXILIARS_DIR . 'paisos/llistat-paisos.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // 17. Viatges
    APP_GESTIO . $url['viatges'] => [
        'view' => APP_INTRANET_DIR . APP_VIATGES_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['viatges'] . '/llistat-viatges' => [
        'view' => APP_INTRANET_DIR . APP_VIATGES_DIR . 'llistat-viatges.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['viatges'] . '/fitxa-viatge/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_VIATGES_DIR . 'fitxa-viatge.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['viatges'] . '/fitxa-espai/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_VIATGES_DIR . 'fitxa-espai.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['viatges'] . '/modifica-espai/{slug}' => [
        'view' => APP_INTRANET_DIR . APP_VIATGES_DIR . 'form-espai.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['viatges'] . '/nou-espai' => [
        'view' => APP_INTRANET_DIR . APP_VIATGES_DIR . 'form-espai.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // Gestió usuaris
    APP_GESTIO . $url['usuaris'] => [
        'view' => APP_INTRANET_DIR . APP_USUARIS_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['usuaris'] . '/llistat-usuaris' => [
        'view' => APP_INTRANET_DIR . APP_USUARIS_DIR . 'llistat-usuaris.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['usuaris'] . '/nou-usuari' => [
        'view' => APP_INTRANET_DIR . APP_USUARIS_DIR . 'form-usuaris.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['usuaris'] . '/modifica-usuari/{id}' => [
        'view' => APP_INTRANET_DIR . APP_USUARIS_DIR . 'form-usuaris.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // RADIO ONLINE

    APP_GESTIO . $url['radio'] => [
        'view' => APP_INTRANET_DIR . APP_RADIO_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['radio'] . '/catmusica' => [
        'view' => APP_INTRANET_DIR . APP_RADIO_DIR . 'catmusica.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    // CURRICULUM
    APP_GESTIO . $url['curriculum'] => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'index.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/nou-perfil" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-perfil.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/modifica-perfil/{id}" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-perfil.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/perfil-cv" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'vista-perfil-cv.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],


    APP_GESTIO . $url['curriculum'] . "/modifica-perfil-i18n/{locale}/{id}" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-perfil-i18n.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/nou-perfil-i18n" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-perfil-i18n.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/perfil-cv-i18n" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'vista-perfil-cv-i18n.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/nou-link" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-links.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/modifica-link/{id}" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-links.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/perfil-links" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'vista-perfil-linsk.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/nova-habilitat" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-habilitats.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/modifica-habilitat/{id}" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-habilitats.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/perfil-habilitats" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'vista-cv-habilitats.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/nova-experiencia" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-experiencia.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/modifica-experiencia/{id}" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-experiencia.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/perfil-experiencies" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'vista-perfil-experiencies.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/perfil-experiencia-professional/{id}" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'vista-cv-experiencia-professional-detalls.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/modifica-experiencia-i18n/{id}" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-experiencia-i18n.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/nova-experiencia-i18n" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-experiencia-i18n.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],


    APP_GESTIO . $url['curriculum'] . "/nou-educacio" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-educacio.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/modifica-educacio/{id}" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-educacio.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/perfil-educacio" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'vista-perfil-educacio.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/nou-educacio-i18n" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-educacio-i18n.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/modifica-educacio-i18n/{id}" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'form-cv-educacio-i18n.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

    APP_GESTIO . $url['curriculum'] . "/perfil-educacio-i18n/{id}" => [
        'view' => APP_INTRANET_DIR . APP_CV_DIR . 'vista-perfil-educacio-i18n.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => false,
        'apiSenseHTML' => false,
        'menu_intranet' => true
    ],

];

// Verificar si el usuario es admin antes de procesar las rutas privadas (admin)
if (isset($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], APP_GESTIO . '/gestio') === 0 || strpos($_SERVER['REQUEST_URI'], APP_GESTIO) === 0)) {
    // Comprobar si es admin antes de acceder a las rutas
    checkIfAdmin();
}

// Devolver las rutas
return $routes;
