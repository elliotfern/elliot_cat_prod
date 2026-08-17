<?php

use App\Config\Database;
use App\Utils\MissatgesAPI;
use App\Utils\Response;
use App\Utils\Uuid;

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
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Error decoding JSON data']);
        exit();
    }

    // Validación
    $errors = [];

    $id = requireField($data, 'id', $errors);
    $servei = requireField($data, 'servei', $errors);
    $usuari = requireField($data, 'usuari', $errors);
    $tipus = requireField($data, 'tipus_id', $errors);

    $web = optionalField($data, 'web');
    $notes = optionalField($data, 'notes');
    $password = optionalField($data, 'password');
    $clau2f = optionalField($data, 'clau2f');

    // Validar format UUID de id i tipus abans de convertir-los a binari
    $regexUuid = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    if ($id !== null && !preg_match($regexUuid, $id)) {
        $errors['id'] = 'format_invalid';
    }

    if ($tipus !== null && !preg_match($regexUuid, $tipus)) {
        $errors['tipus_id'] = 'format_invalid';
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, httpCode: 400);
        exit;
    }

    // Convertir id i tipus (UUID strings) a binari
    $idBinari = Uuid::toBinary($id);
    $tipusIdBinari = Uuid::toBinary($tipus);

    // Asignar valores adicionales
    $timestamp = date('Y-m-d');
    $dateModified = $timestamp;

    // Construcción dinámica del query dependiendo de si se actualiza la contraseña o no
    $query = "UPDATE db_vault SET servei = :servei, usuari = :usuari, tipus_id = :tipus_id, web = :web, notes = :notes, dateModified = :dateModified";

    $binds = [
        ':servei' => [$servei, PDO::PARAM_STR],
        ':usuari' => [$usuari, PDO::PARAM_STR],
        ':tipus_id' => [$tipusIdBinari, PDO::PARAM_LOB],
        ':web' => [$web, PDO::PARAM_STR],
        ':notes' => [$notes, PDO::PARAM_STR],
        ':dateModified' => [$dateModified, PDO::PARAM_STR],
    ];

    // Si el password viene lleno, lo incluimos
    if (!empty($data['password'])) {
        $password = $data['password'];
        $result = generateEncryptedPassword($password, $token);
        $hashedPassword = $result['encryptedPassword'];
        $iv = $result['iv'];
        $query .= ", password = :password";
        $query .= ", iv = :iv";
        $binds[':password'] = [$hashedPassword, PDO::PARAM_STR];
        $binds[':iv'] = [$iv, PDO::PARAM_STR];
    }

    if (!empty($data['clau2f'])) {
        $clau2f = $data['clau2f'];
        $result2 = generateEncryptedPassword($clau2f, $token);
        $hashedclau2f = $result2['encryptedPassword'];
        $iv2f = $result2['iv'];
        $query .= ", clau2f = :clau2f";
        $query .= ", iv2f = :iv2f";
        $binds[':clau2f'] = [$hashedclau2f, PDO::PARAM_STR];
        $binds[':iv2f'] = [$iv2f, PDO::PARAM_STR];
    }

    $query .= " WHERE id = :id";
    $binds[':id'] = [$idBinari, PDO::PARAM_LOB];

    try {
        $stmt = $pdo->prepare($query);

        foreach ($binds as $param => [$value, $type]) {
            $stmt->bindValue($param, $value, $type);
        }

        $stmt->execute();

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
    $response['status'] = 'error';
    header("Content-Type: application/json");
    echo json_encode($response);
}
