<?php

// Función para generar rutas específicas por idioma
function generateLanguageRoutes(array $base_routes, bool $use_languages = true): array
{

    $languages = ['es', 'fr', 'en', 'ca', 'it']; // Idiomas soportados
    $default_language = 'ca'; // Idioma por defecto
    $routes = [];

    // Si no quieres rutas por idioma, solo usa las rutas base sin prefijo
    if (!$use_languages) {
        return $base_routes;
    }

    // Genera las rutas para cada idioma
    foreach ($languages as $lang) {
        foreach ($base_routes as $path => $view) {
            // Se crean las rutas con el prefijo de idioma (por ejemplo, /fr/, /en/, /ca/)
            if ($lang === $default_language) {
                // La ruta raíz para el idioma por defecto se mantiene como está
                $routes[$path] = [
                    'view' => $view,
                    'needs_session' => false,
                    'header_footer' => false,
                    'header_menu_footer' => true
                ];
            } else {
                // Las rutas para otros idiomas tendrán el prefijo de idioma (ej. /fr/, /en/)
                $routes["/{$lang}{$path}"] = [
                    'view' => $view,
                    'needs_session' => false,
                    'header_footer' => false,
                    'header_menu_footer' => true
                ];
            }
        }
    }

    return $routes;
}


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
