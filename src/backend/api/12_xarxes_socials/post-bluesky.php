<?php

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://elliot.cat");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

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

// Verificar si el tipo es 'blueSky'
if (!isset($_GET['type']) || $_GET['type'] !== 'blueSky') {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo no válido']);
    exit();
}

// Credenciales desde variables de entorno
$bluesky_user = $_ENV['BLUESKYUSER'];
$bluesky_pass = $_ENV['BLUESKYAPP'];
$bluesky_did  = $_ENV['BLUESKYDID'];

// Recoger datos del formulario
$mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';
$altImatge = isset($_POST['altImatge']) ? trim($_POST['altImatge']) : '';

if (isset($_FILES["imagen"])) {
    $imagenSubida = isset($_FILES["imagen"]) && $_FILES["imagen"]["size"] > 0;

    if ($_FILES["imagen"]["size"] > 5 * 1024 * 1024) { // 5 MB
        http_response_code(400);
        echo json_encode(['error' => 'La imagen es demasiado grande. El tamaño máximo permitido es 5MB.']);
        exit();
    }
}

if (empty($mensaje)) {
    http_response_code(400);
    echo json_encode(['error' => 'Has d\'escriure un missatge.']);
    exit();
}

// 🔹 Autenticación en Bluesky
$apiUrlAuth = 'https://bsky.social/xrpc/com.atproto.server.createSession';
$authData = json_encode(["identifier" => $bluesky_did, "password" => $bluesky_pass]);

$ch = curl_init($apiUrlAuth);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $authData);
$response = curl_exec($ch);

// Verificar si hubo errores en la solicitud
if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la solicitud CURL: ' . curl_error($ch)]);
    exit();
}

$authInfo = json_decode($response, true);
curl_close($ch);

if (!isset($authInfo['accessJwt'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Error en la autenticación.']);
    exit();
}

$jwt = $authInfo['accessJwt'];
$did = $authInfo['did'];

// 🔹 SUBIR IMAGEN (si existe)
$images = [];
if (isset($_FILES["imagen"])) {
    $apiUrlUpload = 'https://bsky.social/xrpc/com.atproto.repo.uploadBlob';
    $imageTmp = $_FILES["imagen"]["tmp_name"];
    $imageType = mime_content_type($imageTmp);

    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($imageType, $allowedMimeTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'El archivo no es una imagen válida.']);
        exit();
    }

    $imageData = file_get_contents($imageTmp);

    $ch = curl_init($apiUrlUpload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: ' . $imageType,
        'Authorization: Bearer ' . $jwt
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $imageData);
    $response = curl_exec($ch);

    // Verificar si hubo errores en la solicitud
    if (curl_errno($ch)) {
        http_response_code(500);
        echo json_encode(['error' => 'Error en la solicitud CURL al subir la imagen: ' . curl_error($ch)]);
        exit();
    }

    curl_close($ch);
    $uploadResponse = json_decode($response, true);

    if (isset($uploadResponse["blob"]["ref"]['$link'])) {
        $link = $uploadResponse["blob"]["ref"]['$link'];

        // Agregar la imagen al array de imágenes con la estructura correcta
        $images[] = [
            'alt' => $altImatge, // Agrega una descripción alternativa
            'image' => [
                '$type' => 'blob',
                'ref' => [
                    '$link' => $link
                ],
                'mimeType' => $imageType,
                'size' => $_FILES["imagen"]["size"]
            ],
            'aspectRatio' => [
                'width' => 1280, // Ajusta el tamaño o calcula el aspecto según sea necesario
                'height' => 760
            ]
        ];

        // Agregar las imágenes correctamente al embed
        $postData['record']['embed'] = [
            '$type' => 'app.bsky.embed.images',
            'images' => $images
        ];
    } else {
        // Maneja el caso en que no se pudo obtener el link
        http_response_code(500);
        echo json_encode(['error' => 'Error al subir la imagen, falta el enlace del blob', 'detalle' => $uploadResponse]);
        exit();
    }
}

// 🔹 CREAR PUBLICACIÓN EN BLUESKY
$apiUrlPost = 'https://bsky.social/xrpc/com.atproto.repo.createRecord';
$postData = [
    'collection' => 'app.bsky.feed.post',
    'repo' => $did,
    'record' => [
        'text' => $mensaje,
        'createdAt' => date('c'),
        '$type' => 'app.bsky.feed.post'
    ]
];

if (!empty($images)) {
    $postData['record']['embed'] = [
        '$type' => 'app.bsky.embed.images',
        'images' => $images
    ];
}

$ch = curl_init($apiUrlPost);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $jwt
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
$response = curl_exec($ch);

// Verificar si hubo errores en la solicitud
if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la solicitud CURL al crear la publicación: ' . curl_error($ch)]);
    exit();
}

curl_close($ch);

echo json_encode(['success' => '✅ Missatge publicat a Bluesky.', 'response' => json_decode($response)], JSON_PRETTY_PRINT);
