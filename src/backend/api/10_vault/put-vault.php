<?php

use App\Config\Database;
use App\Utils\MissatgesAPI;
use App\Utils\Response;

$db = new Database();
$pdo = $db->getPdo();

header("Content-Type: application/json");

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Metode no permès']);
    exit();
}

// Función para generar una contraseña encriptada y su IV
function generateEncryptedPassword($password, $token)
{
    if (!$token) {
        return ['error' => 'Token de encriptación no definido en .env'];
    }

    $ivLength = openssl_cipher_iv_length('AES-256-CBC');
    $iv = openssl_random_pseudo_bytes($ivLength);

    $encryptedPassword = openssl_encrypt($password, 'AES-256-CBC', $token, 0, $iv);

    return [
        'encryptedPassword' => $encryptedPassword,
        'iv' => base64_encode($iv),
    ];
}

// a) Inserir link
if (isset($_GET['clau'])) {

    // Helpers
    function requireField(array $data, string $key, array &$errors)
    {
        if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === null) {
            $errors[$key] = 'required';
            return null;
        }
        return $data[$key];
    }

    function optionalField(array $data, string $key)
    {
        return (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null)
            ? $data[$key]
            : null;
    }

    // Cargar el archivo .env
    $token = $_ENV['ENCRYPTATION_TOKEN'] ?? null;

    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    // Verificar si se recibieron datos
    if ($data === null) {
        // Error al decodificar JSON
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Error decoding JSON data']);
        exit();
    }

    // Validación
    $errors = [];

    $id = requireField($data, 'id', $errors);
    $servei = requireField($data, 'servei', $errors);
    $usuari = requireField($data, 'usuari', $errors);
    $tipus = requireField($data, 'tipus', $errors);

    $web = optionalField($data, 'web');
    $notes = optionalField($data, 'notes');
    $password = optionalField($data, 'password');
    $clau2f = optionalField($data, 'clau2f');

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, httpCode: 400);
        exit;
    }

    // Asignar valores adicionales
    $timestamp = date('Y-m-d');
    $dateModified = $timestamp;

    // Construcción dinámica del query dependiendo de si se actualiza la contraseña o no
    $query = "UPDATE db_vault SET servei = :servei, usuari = :usuari, tipus = :tipus, web = :web, notes = :notes, dateModified = :dateModified";
    $params = [
        ':servei' => $servei,
        ':usuari' => $usuari,
        ':tipus' => $tipus,
        ':web' => $web,
        ':notes' => $notes,
        ':dateModified' => $dateModified,
    ];

    // Si el password viene lleno, lo incluimos
    if (!empty($data['password'])) {
        $password = $data['password'];
        $result = generateEncryptedPassword($password, $token);
        $hashedPassword = $result['encryptedPassword'];
        $iv = $result['iv'];
        $query .= ", password = :password";
        $query .= ", iv = :iv";
        $params[':password'] = $hashedPassword;
        $params[':iv'] = $iv;
    }

    if (!empty($data['clau2f'])) {
        $clau2f = $data['clau2f'];
        $result2 = generateEncryptedPassword($clau2f, $token);
        $hashedclau2f = $result2['encryptedPassword'];
        $iv2f = $result2['iv'];
        $query .= ", clau2f = :clau2f";
        $query .= ", iv2f = :iv2f";
        $params[':clau2f'] = $hashedclau2f;
        $params[':iv2f'] = $iv2f;
    }

    $query .= " WHERE id = :id";
    $params[':id'] = $id;

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        Response::success(
            MissatgesAPI::success('update'),
            ['id' => $id],
            httpCode: 200
        );
    } catch (PDOException $e) {
        Response::error(
            MissatgesAPI::error('errorBD'),
            [
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ],
            httpCode: 500
        );
    }
} else {
    // response output - data error
    $response['status'] = 'error';

    header("Content-Type: application/json");
    echo json_encode($response);
}
