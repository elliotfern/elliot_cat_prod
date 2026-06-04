<?php

// Llamada a la API con token en los encabezados
function hacerLlamadaAPI(string $url)
{
    $token = $_COOKIE['token'] ?? '';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer {$token}",
            "Accept: application/json",
            "Content-Type: application/json",
            // 👇 para que supere checkReferer($allowedOrigin)
            "Referer: https://elliot.cat",
            "Origin: https://elliot.cat",
        ],
        CURLOPT_TIMEOUT => 15,
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        die("Error en cURL: {$curlErr}");
    }
    if ($status !== 200) {
        die("Error al obtener los datos de la API. HTTP Status Code: {$status}");
    }

    $data = json_decode($response, true);
    if ($data === null) {
        die("Error al decodificar los datos de la API.");
    }

    // acepta payloads con envoltorio {status,message,data} o datos directos
    $payload = $data['data'] ?? $data;

    return $payload;
}
