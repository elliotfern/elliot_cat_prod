<?php

use App\Config\Database;
use App\Vault\Adapters\Inbound\VaultController;
use App\Vault\Core\Services\VaultService;
use App\Vault\Adapters\Outbound\DatabasePasswordRepository;
use App\Infrastructure\Security\Auth\AuthFactory;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Metode no permès']);
    exit();
}

// Verificar si se ha recibido un parámetro válido
if (isset($_GET['llistat_serveis'])) {

    AuthFactory::admin()->handle();

    // Conectar a la base de datos
    $db = new Database();
    $pdo = $db->getPdo();

    // Crear el repositorio
    $passwordRepository = new DatabasePasswordRepository($pdo);

    // Pasar el repositorio a VaultService
    $vaultService = new VaultService($passwordRepository);

    // Pasar el servicio correctamente a VaultController
    $passwordController = new VaultController($vaultService);

    // Llamar al método getPasswords con el ID dinámico
    $passwords = $passwordController->getPasswords();

    // Verificar que hemos obtenido un array de datos
    header('Content-Type: application/json');
    if (is_array($passwords)) {

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $passwords,
            httpCode: 200
        );


        // Devolver los datos como un array JSON
        //echo json_encode($passwords, JSON_PRETTY_PRINT);
    } else {
        // Si no se ha obtenido un array, devolver un error en formato JSON
        Response::error(
            MissatgesAPI::error('not_found'),
            [],
            404
        );
        return;
    }
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
    // Conectar a la base de datos
    $db = new Database();
    $pdo = $db->getPdo();

    // Crear el repositorio
    $passwordRepository = new DatabasePasswordRepository($pdo);

    // Pasar el repositorio a VaultService
    $vaultService = new VaultService($passwordRepository);

    // Pasar el servicio correctamente a VaultController
    $passwordController = new VaultController($vaultService);

    // Obtener el user_id
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

    // Llamar al método getPasswords con el ID dinámico
    $passwords = $passwordController->getPasswordDesencrypt($userId);

    // Verificar que hemos obtenido un array de datos
    header('Content-Type: application/json');
    if (is_array($passwords)) {

        // Devolver los datos como un array JSON
        Response::success(
            message: MissatgesAPI::success('get'),
            data: $passwords,
            httpCode: 200
        );
    } else {
        // Si no se ha obtenido un array, devolver un error en formato JSON
        Response::error(
            MissatgesAPI::error('not_found'),
            [],
            404
        );
        return;
    }

    // Verificar si se ha recibido un parámetro válido
} else if (isset($_GET['serveiId'])) {
    $id = $_GET['serveiId'];

    $db = new Database();
    $pdo = $db->getPdo();

    $sql = <<<SQL
                SELECT v.id, v.servei, v.usuari, v.tipus, v.web, v.notes
                FROM %s AS v
                WHERE v.id = :id;
                SQL;

    $query = sprintf(
        $sql,
        qi(Tables::DB_VAULT, $pdo)
    );

    try {
        $params = [':id' => $id];
        $result = $db->getData($query, $params, true);

        if (empty($result)) {
            Response::error(
                MissatgesAPI::error('not_found'),
                [],
                404
            );
            return;
        }

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $result,
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [$e->getMessage()],
            500
        );
    }

    // Verificar si se ha recibido un parámetro válido
} else if (isset($_GET['tipusServeis'])) {

    $db = new Database();
    $pdo = $db->getPdo();

    $query = "SELECT v.id, v.tipus
    FROM db_vault_type AS v
    ORDER BY v.tipus";

    // Preparar la consulta
    $stmt = $pdo->prepare($query);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit;
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los datos en formato JSON
    echo json_encode($data);

    // GET RUTA para obtener el codigo 2f
    // ruta GET => "/api/vault/?type=codigo2f&id=22"
} elseif ((isset($_GET['type']) && $_GET['type'] == 'codigo2f') && (isset($_GET['id2F']))) {
    // Obtener el ID desde el parámetro GET
    $id = $_GET['id2F'];

    // Conectar a la base de datos
    $db = new Database();
    $pdo = $db->getPdo();

    // Crear el repositorio
    $passwordRepository = new DatabasePasswordRepository($pdo);

    // Pasar el repositorio a VaultService
    $vaultService = new VaultService($passwordRepository);

    // Pasar el servicio correctamente a VaultController
    $passwordController = new VaultController($vaultService);

    // Obtener el user_id
    $userId = isset($_GET['id2F']) ? (int)$_GET['id2F'] : 1;

    // Llamar al método getPasswords con el ID dinámico
    $passwords = $passwordController->getClau2FDesencrypt($userId);

    // Verificar que hemos obtenido un array de datos
    header('Content-Type: application/json');
    if (is_array($passwords)) {
        // Devolver los datos como un array JSON

        Response::success(
            message: MissatgesAPI::success('get'),
            data: $passwords,
            httpCode: 200
        );
    } else {
        // Si no se ha obtenido un array, devolver un error en formato JSON
        Response::error(
            MissatgesAPI::error('not_found'),
            [],
            404
        );
        return;
    }
} else {
    echo json_encode(['error' => 'Invalid ID']);
}
