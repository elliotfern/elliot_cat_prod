<?php
header("Content-Type: application/json");

// Definir el dominio permitido
$allowedOrigin = "https://elliot.cat";

// Llamar a la función para verificar el referer
checkReferer($allowedOrigin);

// Check if the request method is PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
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

    // Ahora puedes acceder a los datos como un array asociativo
    $hasError = false; // Inicializamos la variable $hasError como false

    $servei               = !empty($data['servei']) ? data_input($data['servei']) : ($hasError = true);
    $usuari         = !empty($data['usuari']) ? data_input($data['usuari']) : ($hasError = true);
    $tipus        = !empty($data['tipus']) ? data_input($data['tipus']) : ($hasError = true);
    $web          = !empty($data['web']) ? data_input($data['web']) : ($hasError = false);
    $notes          = !empty($data['notes']) ? data_input($data['notes']) : ($hasError = false);
    $id                  = !empty($data['id']) ? data_input($data['id']) : ($hasError = true);


    // Asignar valores adicionales
    $timestamp = date('Y-m-d');
    $dateModified = $timestamp;


    global $conn;
    /** @var PDO $conn */
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
        $stmt = $conn->prepare($query);
        $stmt->execute($params);

        echo json_encode(['status' => 'success', 'message' => 'Usuari actualitzat correctament']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en l\'actualització de les dades.']);
    }
} else {
    // response output - data error
    $response['status'] = 'error';

    header("Content-Type: application/json");
    echo json_encode($response);
}
