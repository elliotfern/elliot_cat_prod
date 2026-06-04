<?php

use App\Application\Security\NativePasswordHasher;
use App\Application\Usuari\UseCase\LoginUsuariUseCase;
use App\Config\Database;
use App\Config\DatabaseConnection;
use App\Infrastructure\EntryPoint\Http\Usuari\LoginUsuariController;
use App\Infrastructure\Persistence\Usuari\MysqlUsuariRepository;
use App\Infrastructure\Security\Jwt\JwtService;
use App\Utils\Response;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

// Cargar variables de entorno desde .env
$jwtSecret = $_ENV['TOKEN'] ?? null;

$db = new Database();
$pdo = DatabaseConnection::getConnection();

header('Content-Type: application/json; charset=utf-8');


// Verificar si se recibieron datos
if ($slug  === 'login') {
    header('Content-Type: application/json; charset=utf-8');

    $input = json_decode(file_get_contents('php://input'), true);

    $email = $input['email'] ?? null;
    $password = $input['password'] ?? null;

    if (!$email || !$password) {
        Response::error(
            message: 'Email and password are required',
            httpCode: 400
        );
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Response::error(
            message: 'Invalid email format',
            httpCode: 400
        );
    }

    try {

        $repository = new MysqlUsuariRepository($pdo);
        $passwordHasher = new NativePasswordHasher();
        $jwtService = new JwtService($jwtSecret);

        $useCase = new LoginUsuariUseCase(
            $repository,
            $passwordHasher,
            $jwtService
        );

        $controller = new LoginUsuariController($useCase);

        // 👉 només delega
        $controller->execute($email, $password);
    } catch (\Throwable $e) {
        error_log($e->getMessage());
        error_log($e->getTraceAsString());
        http_response_code(500);
        echo json_encode([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        exit;

        Response::error(
            message: $e->getMessage(),
            httpCode: 401
        );
    }
} else {

    // Si no hay resultados, devolver un mensaje de error
    header("Content-Type: application/json");
    echo json_encode(['status' => 'error', 'message' => 'Error endpoint.']);
}
