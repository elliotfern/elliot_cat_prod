<?php

function hacerLlamadaAPI(string $url)
{
    $token = $_COOKIE['token'] ?? '';

    $host = $_SERVER['HTTP_HOST'] ?? 'elliot.cat';

    if (str_contains($host, 'elliot.local')) {
        $origin = 'https://elliot.local';
    } elseif (str_contains($host, 'dev.elliot.cat')) {
        $origin = 'https://dev.elliot.cat';
    } else {
        $origin = 'https://elliot.cat';
    }

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer {$token}",
            "Accept: application/json",
            "Content-Type: application/json",
            "Referer: {$origin}",
            "Origin: {$origin}",
        ],
        CURLOPT_TIMEOUT => 15,
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        die("Error en cURL: {$curlErr}");
    }

    if ($status !== 200) {
        die("Error al obtener los datos de la API.\n" .
            "HTTP Status Code: {$status}\n\n" .
            "URL: {$url}\n\n" .
            "Respuesta API:\n{$response}");
    }

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Error al decodificar los datos de la API.\n\n" .
            "Error JSON: " . json_last_error_msg() . "\n\n" .
            "Respuesta recibida:\n{$response}");
    }

    $payload = $data['data'] ?? $data;

    return $payload;
}
