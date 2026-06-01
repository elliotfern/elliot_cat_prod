<?php

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://elliot.cat");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$mastodonAccessToken = $_ENV['MASTODONTTOKEN']; // Reemplaza con tu token de acceso de Mastodon
$mastodonApiUrl = 'https://mastodont.cat/api/v1/'; // URL base de Mastodon

// Verificar si el tipo es 'mastodon'
if (!isset($_GET['type']) || $_GET['type'] !== 'mastodont') {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo no válido']);
    exit();
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Permitir solo origen autorizado
$allowed_origins = ['https://elliot.cat'];
if (!isset($_SERVER['HTTP_ORIGIN']) || !in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no permitido']);
    exit();
}

// Recoger datos del formulario
$mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';
$imagen = isset($_FILES['imagen']) ? $_FILES['imagen'] : null;
$altImatge = isset($_POST['altImatge']) ? trim($_POST['altImatge']) : '';

if (empty($mensaje) && !$imagen) {
    echo json_encode(['error' => 'Has d\'escriure un missatge.']);
    exit();
}

// Si se ha subido una imagen, la subimos a Mastodon
$mediaId = null;
if ($imagen) {
    // Subir la imagen
    $mediaUrl = $mastodonApiUrl . 'media';
    $imageData = file_get_contents($imagen['tmp_name']);
    $imageType = mime_content_type($imagen['tmp_name']);

    // Crear los datos de la imagen
    $mediaParams = [
        'file' => new CURLFile($imagen['tmp_name'], $imageType, $imagen['name']),
        'description' => $altImatge // Descripción opcional para la imagen
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $mediaUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $mastodonAccessToken
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $mediaParams);
    $mediaResponse = curl_exec($ch);
    curl_close($ch);

    // Verificar si hubo un error al subir la imagen
    if (curl_errno($ch)) {
        echo json_encode(['error' => 'Error al subir la imagen: ' . curl_error($ch)]);
        exit();
    }

    $mediaData = json_decode($mediaResponse, true);



    if (isset($mediaData['id'])) {
        $mediaId = $mediaData['id'];
    } else {
        echo json_encode(['error' => 'No se pudo obtener el ID de la imagen']);
        exit();
    }
}

// Publicar el mensaje con o sin imagen
$statusUrl = $mastodonApiUrl . 'statuses';
$postParams = [
    'status' => $mensaje
];

// Si hay una imagen, incluirla en el mensaje
if ($mediaId) {
    $postParams['media_ids[]'] = $mediaId;
}

// Iniciar la conexión cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $statusUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $mastodonAccessToken
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postParams));

// Ejecutar la solicitud
$response = curl_exec($ch);

// Verificar si hubo un error en la solicitud
if (curl_errno($ch)) {
    echo json_encode(['error' => 'Error al publicar el post: ' . curl_error($ch)]);
    exit();
}

// Mostrar la respuesta del servidor de Mastodon
echo json_encode(['success' => '✅ Missatge publicat a Mastodon.', 'response' => json_decode($response, true)], JSON_PRETTY_PRINT);

// Cerrar la conexión cURL
curl_close($ch);
