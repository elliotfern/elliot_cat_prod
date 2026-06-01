<?php

// Combinar todas las rutas en un solo arreglo
$routes = array_merge(
    require __DIR__ . '/web-publica.php',
    require __DIR__ . '/api.php',
    require __DIR__ . '/area-privada-administradors.php',
);

return $routes;
