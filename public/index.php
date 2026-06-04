<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Incluir configuraciones y rutas
require_once __DIR__ . '/../src/backend/Infrastructure/View/view-types.php';
require_once __DIR__ . '/../src/backend/bootstrap.php';

use App\Infrastructure\Error\ErrorHandler;
use App\Infrastructure\Security\Auth\AuthKernel;
use App\Infrastructure\View\ViewModelFactory;

ErrorHandler::register();

// Obtener la ruta solicitada
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalizar la ruta eliminando barras finales, excepto para la raíz
$requestUri = rtrim($requestUri, '/');
if ($requestUri === '') {
    $requestUri = '/';
}

// Redirección raíz → homepage
if ($requestUri === '/') {
    header('Location: /inici', true, 302);
    exit();
}

// Inicializar una variable para los parámetros de la ruta
$routeParams = [];

// Buscar si la ruta es una ruta dinámica y extraer los parámetros
$routeFound = false;
foreach ($routes as $route => $routeInfo) {
    // Crear un patrón para la ruta dinámica reemplazando los parámetros {param} por expresiones regulares
    $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_-]+)', $route);

    if (preg_match('#^' . $pattern . '$#', $requestUri, $matches)) {
        // Si encontramos la ruta, extraemos los parámetros
        $routeFound = true;
        $routeParams = array_slice($matches, 1);  // El primer elemento es la ruta misma, los parámetros son los siguientes

        // Asignamos la vista asociada a la ruta
        $view = $routeInfo['view'];
        break;
    }
}

// Si la ruta no es encontrada, asignamos la página 404
if (!$routeFound) {
    $viewModel = ViewModelFactory::create();
    $view = './includes/404.php';
    $noHeaderFooter = false;
    $headerMenu = true;
    $apiSenseHTML = false;
} else {
    AuthKernel::boot();

    AuthKernel::handle(
        $routeInfo['needs_admin'] ?? false,
        $routeInfo['needs_session'] ?? false
    );



    $viewModel = ViewModelFactory::create();

    // Determinar si la vista necesita encabezado y pie de página
    $noHeaderFooter = $routeInfo['header_footer'] ?? false;

    // Determinar si la vista necesita el menu del header
    $headerMenu = $routeInfo['header_menu_footer'] ?? false;

    $apiSenseHTML = $routeInfo['apiSenseHTML'] ?? false;
}

// Incluir encabezado y pie de página si no se especifica que no lo tenga
if ($noHeaderFooter) {
    include './includes/header.php';

    // Incluir la vista asociada a la rut

    include $view;

    include './includes/footer-end.php';
} elseif ($headerMenu) {
    include './includes/header.php';
    include './includes/header-menu.php';

    // Incluir la vista asociada a la ruta
    include $view;

    include './includes/footer.php';
    include './includes/footer-end.php';
} elseif ($apiSenseHTML) {
    // Incluir la vista asociada a la ruta
    include $view;
}
