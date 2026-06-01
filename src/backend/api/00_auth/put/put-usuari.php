<?php

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

$nom               = !empty($data['nom']) ? data_input($data['nom']) : ($hasError = true);
$cognom         = !empty($data['cognom']) ? data_input($data['cognom']) : ($hasError = false);
$email        = !empty($data['email']) ? data_input($data['email']) : ($hasError = true);
$userType          = !empty($data['userType']) ? data_input($data['userType']) : ($hasError = true);
$id                  = !empty($data['id']) ? data_input($data['id']) : ($hasError = true);

// Si hay algún error de validación
if ($hasError) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['status' => 'error', 'message' => 'Falten dades obligatòries']);
    exit();
}

global $conn;
/** @var PDO $conn */

// Construcción dinámica del query dependiendo de si se actualiza la contraseña o no
$query = "UPDATE db_users SET nom = :nom, email = :email, cognom = :cognom, userType = :userType";
$params = [
    ':nom' => $nom,
    ':email' => $email,
    ':cognom' => $cognom,
    ':userType' => $userType,
];

// Si el password viene lleno, lo incluimos
if (!empty($data['password'])) {
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => 10]);
    $query .= ", password = :password";
    $params[':password'] = $hashedPassword;
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
