<?php
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);
    http_response_code(204);
    exit;
}

corsAllow(['https://elliot.cat', 'https://dev.elliot.cat']);

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
    $password          = !empty($data['password']) ? data_input($data['password']) : ($hasError = true);
    $clau2f = !empty($data['clau2f']) ? data_input($data['clau2f']) : NULL;

    if (!$hasError) {
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


        // Asignar valores adicionales
        $timestamp = date('Y-m-d');
        $dateCreated = $timestamp;

        global $conn;
        /** @var PDO $conn */
        // Construcción dinámica del query dependiendo de si se actualiza la contraseña o no
        $query = "INSERT INTO db_vault SET servei = :servei, usuari = :usuari, tipus = :tipus, web = :web, notes = :notes, dateCreated = :dateCreated, password = :password, iv = :iv, clau2f = :clau2f, iv2f = :iv2f";
        $params = [
            ':servei' => $servei,
            ':usuari' => $usuari,
            ':tipus' => $tipus,
            ':web' => $web,
            ':notes' => $notes,
            ':dateCreated' => $dateCreated,
            ':password' => $hashedPassword,
            ':iv' => $iv,
            ':clau2f' => $hashedclau2f,
            ':iv2f' => $iv2f,
        ];

        try {
            $stmt = $conn->prepare($query);
            $stmt->execute($params);

            echo json_encode(['status' => 'success', 'message' => 'Vault creat correctament']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error en l\'actualització de les dades.']);
        }
    } else {
        $response['status'] = 'error';

        header("Content-Type: application/json");
        echo json_encode($response);
    }
} else {
    // response output - data error
    $response['status'] = 'error';

    header("Content-Type: application/json");
    echo json_encode($response);
}
