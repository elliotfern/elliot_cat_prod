<?php

use App\Config\Database;
use App\Utils\MissatgesAPI;
use App\Utils\Response;
use App\Utils\Uuid;
use Ramsey\Uuid\Uuid as Ramsey;

$db = new Database();
$pdo = $db->getPdo();

header("Content-Type: application/json");

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

    $servei = requireField($data, 'servei', $errors);
    $usuari = requireField($data, 'usuari', $errors);
    $tipus = requireField($data, 'tipus', $errors);

    $web = optionalField($data, 'web');
    $notes = optionalField($data, 'notes');
    $password = optionalField($data, 'password');
    $clau2f = optionalField($data, 'clau2f');

    // Validar format UUID de tipus abans de convertir-lo a binari
    if ($tipus !== null && !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $tipus)) {
        $errors['tipus'] = 'format_invalid';
    }

    if (!empty($errors)) {
        Response::error(MissatgesAPI::error('validacio'), $errors, httpCode: 400);
        exit;
    }

    $result = generateEncryptedPassword($password, $token);
    $hashedPassword = $result['encryptedPassword'];
    $iv = $result['iv'];

    if ($clau2f !== NULL) {
        $result2 = generateEncryptedPassword($clau2f, $token);
        $hashedclau2f = $result2['encryptedPassword'];
        $iv2f = $result2['iv'];
    } else {
        $hashedclau2f = NULL;
        $iv2f = NULL;
    }

    // Generar nou UUID v7 pel id
    $novaId = Ramsey::uuid7();
    $idBinari = Uuid::toBinary($novaId->toString());

    // Convertir tipus (UUID string) a binari per tipus_id
    $tipusIdBinari = Uuid::toBinary($tipus);

    // Asignar valores adicionales
    $timestamp = date('Y-m-d');
    $dateCreated = $timestamp;

    $query = "INSERT INTO db_vault SET id = :id, servei = :servei, usuari = :usuari, tipus_id = :tipus_id, web = :web, notes = :notes, dateCreated = :dateCreated, password = :password, iv = :iv, clau2f = :clau2f, iv2f = :iv2f";
    $stmt = $pdo->prepare($query);

    $stmt->bindValue(':id', $idBinari, PDO::PARAM_LOB);
    $stmt->bindValue(':servei', $servei);
    $stmt->bindValue(':usuari', $usuari);
    $stmt->bindValue(':tipus_id', $tipusIdBinari, PDO::PARAM_LOB);
    $stmt->bindValue(':web', $web);
    $stmt->bindValue(':notes', $notes);
    $stmt->bindValue(':dateCreated', $dateCreated);
    $stmt->bindValue(':password', $hashedPassword);
    $stmt->bindValue(':iv', $iv);
    $stmt->bindValue(':clau2f', $hashedclau2f);
    $stmt->bindValue(':iv2f', $iv2f);

    try {
        $stmt->execute();

        Response::success(
            MissatgesAPI::success('create'),
            ['id' => $novaId->toString()],
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
