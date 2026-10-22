<?php

function routeUser(string $viewPath, array $overrides = []): array
{

    // Àrea d'usuaris: qualsevol usuari autenticat
    $defaultUserConfig = [
        'needs_session' => true,
        'needs_admin'   => false,
        'header_footer' => false,
        'header_menu_footer' => true,
        'apiSenseHTML' => false,
    ];

    return array_merge(
        $defaultUserConfig,
        $overrides,
        ['view' => $viewPath]
    );
}

$routes = [

    // 01. Homepage
    APP_AREA_USUARIS => routeUser(APP_AREA_USUARIS_DIR . '/index.php'),
    APP_AREA_USUARIS . '/admin' => routeUser(APP_AREA_USUARIS_DIR . '/index.php'),

];

return $routes;
