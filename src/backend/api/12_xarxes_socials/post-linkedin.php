<?php

$client_id = $_ENV['LINKEDIN_CLIENDID'];
$client_secret = $_ENV['LINKEDIN_SECRET'];
$redirect_uri = $_ENV['LINKEDIN_REDIRECT'];
$code = $_ENV['LINKEDIN_CODE'];

$url = "https://www.linkedin.com/oauth/v2/accessToken";
$data = [
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $redirect_uri,
    'client_id' => $client_id,
    'client_secret' => $client_secret
];

$options = [
    'http' => [
        'header'  => "Content-Type: application/x-www-form-urlencoded",
        'method'  => 'POST',
        'content' => http_build_query($data)
    ]
];

$context  = stream_context_create($options);
$result = @file_get_contents($url, false, $context);

if ($result === FALSE) {
    // Capturar error
    $error = error_get_last();
    echo "Error en la solicitud: " . $error['message'];
} else {
    // Convertir respuesta a array
    $response = json_decode($result, true);

    if (isset($response['error'])) {
        // Mostrar mensaje de error de LinkedIn
        echo "Error de LinkedIn: " . $response['error_description'];
    } else {
        // Mostrar Access Token
        echo "Access Token: " . $response['access_token'];
        $access_token = $response['access_token'];
    }
}



$url = "https://api.linkedin.com/v2/me";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $access_token",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['id'])) {
    echo "Tu ID de LinkedIn es: " . $data['id'];
} else {
    echo "Error al obtener el ID: " . $response;
}


/*

$access_token = $response['access_token']; // Access Token obtenido
$author = "urn:li:person:TUIDDELINKEDIN"; // Reemplázalo con tu ID de LinkedIn

$url = "https://api.linkedin.com/v2/ugcPosts";

$data = [
    "author" => $author,
    "lifecycleState" => "PUBLISHED",
    "specificContent" => [
        "com.linkedin.ugc.ShareContent" => [
            "shareCommentary" => [
                "text" => "¡Hola LinkedIn! Este es mi primer post desde PHP 🚀"
            ],
            "shareMediaCategory" => "NONE"
        ]
    ],
    "visibility" => [
        "com.linkedin.ugc.MemberNetworkVisibility" => "PUBLIC"
    ]
];

$options = [
    "http" => [
        "header"  => "Authorization: Bearer $access_token\r\n" .
                     "Content-Type: application/json\r\n" .
                     "X-Restli-Protocol-Version: 2.0.0\r\n",
        "method"  => "POST",
        "content" => json_encode($data)
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
$response = json_decode($result, true);

print_r($response);

*/