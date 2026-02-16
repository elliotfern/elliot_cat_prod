<?php

// Función para generar rutas específicas por idioma (SIEMPRE con prefijo)
function generateLanguageRoutes(array $base_routes, array $languages = ['ca', 'es', 'en', 'fr', 'it']): array
{
    $routes = [];

    foreach ($languages as $lang) {
        foreach ($base_routes as $path => $viewOrConfig) {

            // Normaliza path: siempre empieza por "/"
            $path = '/' . ltrim((string)$path, '/');

            // Si el base_routes es string => view simple
            // Si es array => config completo (permite needs_session, header_footer, etc.)
            if (is_array($viewOrConfig)) {
                $cfg = $viewOrConfig;
                if (!isset($cfg['view'])) {
                    throw new RuntimeException("Route config for '$path' missing 'view'");
                }
            } else {
                $cfg = [
                    'view' => $viewOrConfig,
                    'needs_session' => false,
                    'needs_admin' => false,
                    'header_footer' => false,
                    'header_menu_footer' => true,
                    'apiSenseHTML' => false,
                    'menu_intranet' => false,
                ];
            }

            // ✅ SIEMPRE prefijo idioma
            $routes["/{$lang}{$path}"] = $cfg;
        }
    }

    return $routes;
}

// helper local (puedes moverlo a utils si lo usas en más endpoints)
function hexToUuidText(string $hex): string
{
    $h = strtolower(trim($hex));
    $h = str_replace('-', '', $h);
    if (!preg_match('/^[0-9a-f]{32}$/', $h)) return $hex;

    return substr($h, 0, 8) . '-' .
        substr($h, 8, 4) . '-' .
        substr($h, 12, 4) . '-' .
        substr($h, 16, 4) . '-' .
        substr($h, 20, 12);
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
