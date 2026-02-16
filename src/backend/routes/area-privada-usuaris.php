<?php

// Función que verifica si el usuario es Admin
function checkIfUsuari()
{
    if (!isUserUsuari()) {
        // Si no es admin, redirigimos al login o a una página de acceso denegado
        header('Location: /ca/entrada');
        exit;
    }
}

// Rutas principales sin idioma explícito (solo para el idioma por defecto)
$routes = [
    APP_AREA_USUARIS => [
        'view' =>  APP_AREA_USUARIS_DIR . 'admin.php',
        'needs_session' => true,
        'header_footer' => false,
        'header_menu_footer' => true,
        'apiSenseHTML' => false,
        'menu_intranet' => false,
    ],

];

// Verificar si el usuario es User type 2 antes de procesar las rutas privadas (admin)
if (isset($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], APP_AREA_USUARIS . '/usuaris') === 0 || strpos($_SERVER['REQUEST_URI'], APP_AREA_USUARIS) === 0)) {
    // Comprobar si es admin antes de acceder a las rutas
    checkIfUsuari();
}

// Devolver las rutas
return $routes;
