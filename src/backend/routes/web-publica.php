<?php

$defaultPublicConfig = [
    'needs_session' => false,
    'needs_admin'   => false,
    'header_footer' => false,
    'header_menu_footer' => true,
    'apiSenseHTML' => false,
];

function route_public(string $viewPath, array $overrides = []): array
{
    global $defaultPublicConfig;

    return array_merge(
        $defaultPublicConfig,
        $overrides,
        ['view' => $viewPath]
    );
}

$routes = [

    // HOMEPAGE (alias coherente)
    '/'        => route_public('./web-publica/index.php'),
    '/home'    => route_public('./web-publica/index.php'),
    '/inici'   => route_public('./web-publica/index.php'),

    // Login / registro
    '/entrada'     => route_public('./web-publica/autenticacio-usuaris/login.php'),
    '/nou-usuari'  => route_public('./web-publica/autenticacio-usuaris/registre-usuari.php'),

    // HISTORIA OBERTA
    '/historia' => route_public('./web-publica/historia.php'),
    '/historia/curs/{slug}' => route_public('./web-publica/curs.php'),
    '/historia/article/{slug}' => route_public('./web-publica/article.php'),
];

return $routes;
