<?php

use App\Utils\Response;
use App\Utils\MissatgesAPI;

/** @var array $routeParams */
$slug = $routeParams[0] ?? null;

// Siempre JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    http_response_code(204);
    exit;
}


// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    corsAllow(['https://elliot.cat', 'https://dev.elliot.cat', 'https://elliot.local']);
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Cat Musica
// ruta GET => "/api/radio/get/catmusica"
if ($slug === 'catmusica') {
    header('Content-Type: application/json');

    // URL de la API original (reemplaza con tu queryKey completa)
    $api_url = 'https://api.3cat.cat/arafem?_format=json&cadena=cm&r=yes&tipus=radio&version=1.0';

    $response = file_get_contents($api_url);

    if ($response === FALSE) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener datos de la API externa']);
        exit;
    }

    Response::success(
        message: MissatgesAPI::success('get'),
        data: $response,
        httpCode: 200
    );

    // Cat Info
    // ruta GET => "/api/radio/get/catinfo"
} else if ($slug === 'catinfo') {
    header('Content-Type: application/json');

    // URL de la API original (reemplaza con tu queryKey completa)
    $api_url = 'https://api.3cat.cat/arafem?_format=json&cadena=ci&geo=int&tipus=radio&version=1.0';

    $response = file_get_contents($api_url);

    if ($response === FALSE) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener datos de la API externa']);
        exit;
    }

    Response::success(
        message: MissatgesAPI::success('get'),
        data: $response,
        httpCode: 200
    );

    // icatfm
    // ruta GET => "/api/radio/get/icatfm"
} else if ($slug === 'icatfm') {
    header('Content-Type: application/json');

    // URL de la API original (reemplaza con tu queryKey completa)
    $api_url = 'https://api.3cat.cat/arafem?_format=json&cadena=ic&geo=int&tipus=radio&version=1.0';

    $response = file_get_contents($api_url);

    if ($response === FALSE) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener datos de la API externa']);
        exit;
    }

    Response::success(
        message: MissatgesAPI::success('get'),
        data: $response,
        httpCode: 200
    );
    // BBC Four
    // ruta GET => "/api/radio/get/bbc4"
} else if ($slug === 'bbc4') {
    header('Content-Type: application/json');

    // Fragmento de ejemplo para get-radio.php: cómo llamar a la API de BBC Radio 4
    // sin que devuelva 403 Forbidden. La clave es enviar un User-Agent identificable
    // (la BBC bloquea peticiones "anónimas" sin cabeceras a su API interna RMS).


    $url = 'https://rms.api.bbc.co.uk/v2/my/broadcasts/sub-services/poll/bbc_radio_fourfm?experience=domestic&offset=0&limit=1';

    $opts = [
        "http" => [
            "method" => "GET",
            "header" => implode("\r\n", [
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
                "Accept: application/json",
            ]),
            "timeout" => 10,
            "ignore_errors" => true, // para poder leer también el cuerpo si la BBC devuelve un error
        ],
    ];

    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        // Aquí seguirías con tu manejo de error habitual (Response::error + exit)
        // pero ahora sí debería llegar contenido en vez de false.
    }

    // Comprobar el código HTTP real devuelto (viene en $http_response_header tras el file_get_contents)
    $statusLine = $http_response_header[0] ?? '';
    preg_match('/\d{3}/', $statusLine, $matches);
    $statusCode = isset($matches[0]) ? (int) $matches[0] : 0;

    if ($statusCode !== 200) {
        // Log opcional: error_log("BBC API devolvió $statusCode: $response");
        // Aquí tu Response::error(...) + exit() habitual
    }

    $data = json_decode($response, true);


    if ($data === FALSE) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener datos de la API externa']);
        exit;
    }

    Response::success(
        message: MissatgesAPI::success('get'),
        data: $data,
        httpCode: 200
    );


    // BBC Six
    // ruta GET => "/api/radio/get/bbc6"
} else if ($slug === 'bbc6') {
    header('Content-Type: application/json');

    // Fragmento de ejemplo para get-radio.php: cómo llamar a la API de BBC Radio 4
    // sin que devuelva 403 Forbidden. La clave es enviar un User-Agent identificable
    // (la BBC bloquea peticiones "anónimas" sin cabeceras a su API interna RMS).


    $url = 'https://rms.api.bbc.co.uk/v2/services/bbc_6music/segments/latest?experience=domestic&limit=4';

    $opts = [
        "http" => [
            "method" => "GET",
            "header" => implode("\r\n", [
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
                "Accept: application/json",
            ]),
            "timeout" => 10,
            "ignore_errors" => true, // para poder leer también el cuerpo si la BBC devuelve un error
        ],
    ];

    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        // Aquí seguirías con tu manejo de error habitual (Response::error + exit)
        // pero ahora sí debería llegar contenido en vez de false.
    }

    // Comprobar el código HTTP real devuelto (viene en $http_response_header tras el file_get_contents)
    $statusLine = $http_response_header[0] ?? '';
    preg_match('/\d{3}/', $statusLine, $matches);
    $statusCode = isset($matches[0]) ? (int) $matches[0] : 0;

    if ($statusCode !== 200) {
        // Log opcional: error_log("BBC API devolvió $statusCode: $response");
        // Aquí tu Response::error(...) + exit() habitual
    }

    $data = json_decode($response, true);


    if ($data === FALSE) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener datos de la API externa']);
        exit;
    }

    Response::success(
        message: MissatgesAPI::success('get'),
        data: $data,
        httpCode: 200
    );

    // France Culture
    // ruta GET => "/api/radio/get/franceculture"
} else if ($slug === 'france-culture') {
    header('Content-Type: application/json');

    $url = 'https://www.radiofrance.fr/_app/remote/di23tz/getLive?payload=W1siX19za3JhbyIsMV0seyJicmFuZE5hbWUiOjIsInZlcnNpb24iOjN9LCJmcmFuY2VjdWx0dXJlIiwiMjAyNi0wNS0xMiJd';

    $opts = [
        "http" => [
            "method" => "GET",
            "header" => implode("\r\n", [
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
                "Accept: application/json",
            ]),
            "timeout" => 10,
            "ignore_errors" => true, // para poder leer también el cuerpo si la BBC devuelve un error
        ],
    ];

    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        // Aquí seguirías con tu manejo de error habitual (Response::error + exit)
        // pero ahora sí debería llegar contenido en vez de false.
    }

    // Comprobar el código HTTP real devuelto (viene en $http_response_header tras el file_get_contents)
    $statusLine = $http_response_header[0] ?? '';
    preg_match('/\d{3}/', $statusLine, $matches);
    $statusCode = isset($matches[0]) ? (int) $matches[0] : 0;

    if ($statusCode !== 200) {
        // Log opcional: error_log("BBC API devolvió $statusCode: $response");
        // Aquí tu Response::error(...) + exit() habitual
    }

    $data = json_decode($response, true);


    if ($data === FALSE) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener datos de la API externa']);
        exit;
    }

    Response::success(
        message: MissatgesAPI::success('get'),
        data: $data,
        httpCode: 200
    );

    // France Inter
    // ruta GET => "/api/radio/get/france-inter"
} else if ($slug === 'france-inter') {
    header('Content-Type: application/json');

    $url = 'https://www.radiofrance.fr/_app/remote/di23tz/getLive?payload=W1siX19za3JhbyIsMV0seyJicmFuZE5hbWUiOjIsInZlcnNpb24iOjN9LCJmcmFuY2VpbnRlciIsIjIwMjYtMDUtMTIiXQ';

    $opts = [
        "http" => [
            "method" => "GET",
            "header" => implode("\r\n", [
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
                "Accept: application/json",
            ]),
            "timeout" => 10,
            "ignore_errors" => true, // para poder leer también el cuerpo si la BBC devuelve un error
        ],
    ];

    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        // Aquí seguirías con tu manejo de error habitual (Response::error + exit)
        // pero ahora sí debería llegar contenido en vez de false.
    }

    // Comprobar el código HTTP real devuelto (viene en $http_response_header tras el file_get_contents)
    $statusLine = $http_response_header[0] ?? '';
    preg_match('/\d{3}/', $statusLine, $matches);
    $statusCode = isset($matches[0]) ? (int) $matches[0] : 0;

    if ($statusCode !== 200) {
        // Log opcional: error_log("BBC API devolvió $statusCode: $response");
        // Aquí tu Response::error(...) + exit() habitual
    }

    $data = json_decode($response, true);


    if ($data === FALSE) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener datos de la API externa']);
        exit;
    }

    Response::success(
        message: MissatgesAPI::success('get'),
        data: $data,
        httpCode: 200
    );

    // France Musique
    // ruta GET => "/api/radio/get/france-musique"
} else if ($slug === 'france-musique') {
    header('Content-Type: application/json');

    $url = 'https://www.radiofrance.fr/_app/remote/di23tz/getLive?payload=W1siX19za3JhbyIsMV0seyJicmFuZE5hbWUiOjIsInZlcnNpb24iOjN9LCJmcmFuY2VtdXNpcXVlIiwiMjAyNi0wNS0xMiJd';

    $opts = [
        "http" => [
            "method" => "GET",
            "header" => implode("\r\n", [
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
                "Accept: application/json",
            ]),
            "timeout" => 10,
            "ignore_errors" => true, // para poder leer también el cuerpo si la BBC devuelve un error
        ],
    ];

    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        // Aquí seguirías con tu manejo de error habitual (Response::error + exit)
        // pero ahora sí debería llegar contenido en vez de false.
    }

    // Comprobar el código HTTP real devuelto (viene en $http_response_header tras el file_get_contents)
    $statusLine = $http_response_header[0] ?? '';
    preg_match('/\d{3}/', $statusLine, $matches);
    $statusCode = isset($matches[0]) ? (int) $matches[0] : 0;

    if ($statusCode !== 200) {
        // Log opcional: error_log("BBC API devolvió $statusCode: $response");
        // Aquí tu Response::error(...) + exit() habitual
    }

    $data = json_decode($response, true);


    if ($data === FALSE) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener datos de la API externa']);
        exit;
    }

    Response::success(
        message: MissatgesAPI::success('get'),
        data: $data,
        httpCode: 200
    );
} else {
    //
}
