<?php

// Rutas base (SIN prefijo idioma). Se generarán /ca..., /en..., /es..., /fr..., /it...
$base_routes = [

    // HOMEPAGE
    '/homepage' => 'public/web-publica/index.php',

    // Login / registro (público)
    '/entrada' => 'public/web-publica/autenticacio-usuaris/login.php',
    '/nou-usuari' => 'public/web-publica/autenticacio-usuaris/registre-usuari.php',

    // HISTORIA OBERTA
    '/historia' => 'public/web-publica/historia.php',
    '/historia/curs/{slug}' => 'public/web-publica/curs.php',
    '/historia/article/{slug}' => 'public/web-publica/article.php',

    // BIBLIOTECA (público)
    $url['biblioteca'] => APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'index.php',
    $url['biblioteca'] . '/llistat-llibres' => APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'vista-llistat-llibres.php',
    $url['biblioteca'] . '/llistat-autors' => APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'vista-llistat-autors.php',
    $url['biblioteca'] . '/fitxa-llibre/{slug}' => APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'vista-llibre.php',
    $url['biblioteca'] . '/fitxa-autor/{slug}' => APP_INTRANET_DIR . APP_BIBLIOTECA_DIR . 'vista-autor.php',

    // VIATGES (público)
    $url['viatges'] => APP_INTRANET_DIR . APP_VIATGES_DIR . 'index.php',
    $url['viatges'] . '/llistat-viatges' => APP_INTRANET_DIR . APP_VIATGES_DIR . 'llistat-viatges.php',
    $url['viatges'] . '/fitxa-viatge/{slug}' => APP_INTRANET_DIR . APP_VIATGES_DIR . 'fitxa-viatge.php',
    $url['viatges'] . '/fitxa-espai/{slug}' => APP_INTRANET_DIR . APP_VIATGES_DIR . 'fitxa-espai.php',

    // CINEMA (público)
    $url['cinema'] => APP_INTRANET_DIR . APP_CINEMA_DIR . 'index.php',
    $url['cinema'] . '/llistat-pelicules' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-llistat-pelicules.php',
    $url['cinema'] . '/llistat-series' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-llistat-series.php',
    $url['cinema'] . '/llistat-directors' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-llistat-directors.php',
    $url['cinema'] . '/llistat-actors' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-llistat-actors.php', // ✅ FIX typo
    $url['cinema'] . '/llistat-obres-teatre' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-llistat-teatre.php',

    $url['cinema'] . '/fitxa-actor/{slug}' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-actor.php',
    $url['cinema'] . '/fitxa-director/{slug}' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-director.php', // ✅ FIX missing "/"
    $url['cinema'] . '/fitxa-pelicula/{slug}' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-pelicula.php',
    $url['cinema'] . '/fitxa-serie/{slug}' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-serie.php',
    $url['cinema'] . '/fitxa-teatre/{slug}' => APP_INTRANET_DIR . APP_CINEMA_DIR . 'vista-teatre.php',

    // PERSONES (público)
    $url['persones'] => APP_INTRANET_DIR . APP_PERSONES_DIR . 'index.php',
    $url['persones'] . '/llistat-persones' => APP_INTRANET_DIR . APP_PERSONES_DIR . 'index.php',

    // BLOG (público)
    $url['blog'] => APP_INTRANET_DIR . APP_BLOG_DIR . 'index.php',
    $url['blog'] . '/article/{slug}' => APP_INTRANET_DIR . APP_BLOG_DIR . 'fitxa-article.php',

    // RSS (público)
    $url['rss'] => APP_INTRANET_DIR . APP_RSS_DIR . 'index.php',
];

// ✅ Genera TODAS las rutas con prefijo de idioma
$routes = generateLanguageRoutes($base_routes, ['ca', 'es', 'en', 'fr', 'it']);

return $routes;
