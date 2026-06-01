<?php

// PER USAR EN LES URLS
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocal = in_array($host, ['localhost', '127.0.0.1']);

if ($isLocal) {
    define('APP_URL', 'http://localhost');
} else {
    define('APP_URL', 'https://elliot.cat');
}
