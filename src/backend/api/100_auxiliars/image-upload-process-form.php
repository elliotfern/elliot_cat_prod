<?php
/*
 * BACKEND LIBRARY
 * FUNCIONES INSERTAR LIBRO
 * @update_book_ajax
 */

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Verificar origen permitido
$allowed_origins = ['https://elliot.cat'];
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
} else {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no permitido']);
    exit();
}

// Verificar si se recibió un archivo
if (empty($_FILES['fileToUpload']) || $_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'No se subió ningún archivo o hubo un error en la carga.',
        'error_code' => $_FILES['fileToUpload']['error'] ?? 'Sin información'
    ]);
    exit();
}

// Configuración de rutas
$servidorMedia = '/home/epgylzqu/media.elliotfern.com/img/';

// Verificar y sanitizar el tipo de imagen
$type = isset($_POST['typeImg']) ? (int)$_POST['typeImg'] : 0;
$allowed_types = [
    1 => 'biblioteca-autor',
    2 => 'biblioteca-llibre',
    3 => 'historia-imatge',
    4 => 'historia-esdeveniment',
    5 => 'historia-persona',
    6 => 'historia-organitzacio',
    7 => 'cinema-serie',
    8 => 'cinema-pelicula',
    9 => 'cinema-actor',
    10 => 'historia-imatge-min',
    11 => 'viatge',
    12 => 'historia-mapa',
    13 => 'blog',
    14 => 'cinema-director',
    15 => 'historia-infografia',
    16 => 'historia-cronologia',
    17 => 'viatge-espai',
    18 => 'usuaris-avatar',
];
$typeName = $allowed_types[$type] ?? 'elliotfern';

// Crear el directorio de destino si no existe
$target_dir = rtrim($servidorMedia, '/') . '/' . $typeName . '/';
if (!file_exists($target_dir)) {
    if (!mkdir($target_dir, 0777, true)) {
        echo json_encode(['error' => "No se pudo crear el directorio $target_dir."]);
        exit();
    }
}

// Verificar permisos de escritura
if (!is_writable($target_dir)) {
    echo json_encode(['error' => "El directorio $target_dir no tiene permisos de escritura."]);
    exit();
}

// Validar el archivo
$file = $_FILES['fileToUpload'];
$allowed_mime_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$max_file_size = 2 * 1024 * 1024; // 2 MB

if ($file['size'] > $max_file_size || !in_array($file['type'], $allowed_mime_types)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'El archivo es demasiado grande o no es un tipo de imagen permitido.',
    ]);
    exit();
}

// Generar un nombre único para el archivo
$uniqueName = basename($file['name']);
$targetFile = $target_dir . $uniqueName;

// Mover el archivo al servidor
if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Hubo un problema al mover el archivo al servidor.',
    ]);
    exit();
}

// Insertar datos en la base de datos
try {
    $nameImg = pathinfo($uniqueName, PATHINFO_FILENAME);

    $nom = !empty($_POST['nom']) ? data_input($_POST['nom']) : ($hasError = true);
    $alt = !empty($_POST['alt']) ? data_input($_POST['alt']) : ($hasError = true);
    $dateCreated = date('Y-m-d');

    // Usar una conexión global para PDO
    global $conn;
    $sql = "INSERT INTO db_img (nameImg, typeImg, alt, nom, dateCreated) 
            VALUES (:nameImg, :typeImg, :alt, :nom, :dateCreated)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":nameImg", $nameImg, PDO::PARAM_STR);
    $stmt->bindParam(":nom", $nom, PDO::PARAM_STR);
    $stmt->bindParam(":typeImg", $type, PDO::PARAM_INT);
    $stmt->bindParam(":alt", $alt, PDO::PARAM_STR);
    $stmt->bindParam(":dateCreated", $dateCreated, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'El archivo se ha subido y registrado correctamente.',
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Hubo un problema al insertar en la base de datos.',
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al interactuar con la base de datos.',
        'error' => $e->getMessage(),
    ]);
}
