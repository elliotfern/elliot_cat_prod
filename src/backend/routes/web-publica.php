<?php

function route_public(string $viewPath, array $overrides = []): array
{
    $defaultPublicConfig = [
        'needs_session' => false,
        'needs_admin'   => false,
        'header_footer' => false,
        'header_menu_footer' => true,
        'apiSenseHTML' => false,
    ];

    return array_merge(
        $defaultPublicConfig,
        $overrides,
        ['view' => $viewPath]
    );
}

$routes = [

    // HOMEPAGE (alias coherente)
    '/'        => route_public('./web-publica/index.php'),

    // Login / registro
    '/entrada'     => route_public('./web-publica/autenticacio-usuaris/login.php'),
    '/nou-usuari'  => route_public('./web-publica/autenticacio-usuaris/registre-usuari.php'),

    // HISTORIA OBERTA
    '/historia' => route_public('./web-publica/historia.php'),
    '/historia/curs/{slug}' => route_public('./web-publica/curs.php'),
    '/historia/article/{slug}' => route_public('./web-publica/article.php'),

    // GALERIA IMATGES
    '/imatges/galeria/{slug}' => route_public('./web-publica/galeria-imatges.php'),

    // BLOG
    '/blog' => route_public('./web-publica/blog.php'),
    '/blog/{slug}' => route_public('./web-publica/article-blog.php'),

    // TEMES LEGALS FOOTER
    '/autor' => route_public('./web-publica/autor.php'),
    '/politica-privacitat' => route_public('./web-publica/politica-privacitat.php'),
    '/compromis-qualitat' => route_public('./web-publica/compromis-qualitat.php'),
    '/contacte' => route_public('./web-publica/contacte.php'),

];

return $routes;
