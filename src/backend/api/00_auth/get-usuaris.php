<?php

use App\Config\Database;
use App\Config\DatabaseConnection;
use App\Utils\Response;
use App\Application\Usuari\UseCase\MeUsuariUseCase;
use App\Infrastructure\EntryPoint\Http\Usuari\MeUsuariController;
use App\Infrastructure\Security\Jwt\JwtService;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

// Cargar variables de entorno desde .env
$jwtSecret = $_ENV['TOKEN'] ?? null;

$db = new Database();
$pdo = DatabaseConnection::getConnection();

header('Content-Type: application/json; charset=utf-8');

// /api/usuaris/get/me
if ($slug === 'me') {
    try {

        $jwtSecret = $_ENV['TOKEN'] ?? null;

        $jwtService = new JwtService($jwtSecret);

        $useCase = new MeUsuariUseCase(
            $jwtService
        );

        $controller = new MeUsuariController(
            $useCase
        );

        $controller->execute();
    } catch (\RuntimeException $e) {

        Response::error(
            message: $e->getMessage(),
            httpCode: 401
        );
    }
} else if ((isset($_GET['logOut']))) {
    // Verifica que el usuario esté autenticado
    session_start();

    $isLocal = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);

    $arr_cookie_options = [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => $isLocal ? '' : '.elliot.cat',
        'secure' => !$isLocal,
        'httponly' => true,
        'samesite' => $isLocal ? 'Lax' : 'None'
    ];

    //Elimina les cookies
    setcookie('token', '', $arr_cookie_options);

    // Además, puedes destruir la sesión si estás utilizando sesiones en PHP
    session_unset();    // Elimina todas las variables de sesión
    session_destroy();  // Destruye la sesión

    // Respuesta en formato JSON o redirige
    Response::success(
        message: 'OK',
        httpCode: 200
    );
}
