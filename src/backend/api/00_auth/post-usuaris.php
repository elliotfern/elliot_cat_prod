<?php

use App\Application\Security\NativePasswordHasher;
use App\Application\Usuari\UseCase\CreateUsuariUseCase;
use App\Application\Usuari\UseCase\LoginUsuariUseCase;
use App\Config\Database;
use App\Config\DatabaseConnection;
use App\Infrastructure\EntryPoint\Http\Usuari\CreateUsuariController;
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


// ENDPOINT DE LOGIN
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

    // ENDPOINT DE REGISTRE USUARI
} else if ($slug  === 'registre') {

    header('Content-Type: application/json; charset=utf-8');

    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        Response::error(
            message: 'Invalid JSON input',
            httpCode: 400
        );
        exit;
    }

    $email = $input['email'] ?? null;
    $password = $input['password'] ?? null;
    $nom = $input['nom'] ?? null;
    $cognom = $input['cognom'] ?? null;
    $acceptaPrivacitat = $input['acceptaPrivacitat'] ?? null;

    if (
        !is_string($email) || trim($email) === '' ||
        !is_string($password) || $password === '' ||
        !is_string($nom) || trim($nom) === '' ||
        !is_string($cognom) || trim($cognom) === ''
    ) {
        Response::error(
            message: 'All fields are required',
            httpCode: 400
        );
        exit;
    }

    if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
        Response::error(
            message: 'Invalid email format',
            httpCode: 400
        );
        exit;
    }

    try {
        $repository = new MysqlUsuariRepository($pdo);
        $passwordHasher = new NativePasswordHasher();

        $useCase = new CreateUsuariUseCase(
            $repository,
            $passwordHasher
        );

        $controller = new CreateUsuariController($useCase);

        $controller->execute(
            email: $email,
            password: $password,
            nom: $nom,
            cognom: $cognom,
            acceptaPrivacitat: $acceptaPrivacitat
        );
    } catch (\Throwable $e) {
        error_log($e->getMessage());

        Response::error(
            message: 'Unable to create account',
            httpCode: 500
        );
    }

    exit;
} else {

    // Si no hay resultados, devolver un mensaje de error
    header("Content-Type: application/json");
    echo json_encode(['status' => 'error', 'message' => 'Error endpoint.']);
}
