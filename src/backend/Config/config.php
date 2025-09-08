<?php

// Cargar librerías externas
require_once __DIR__ . '/../../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();
require_once __DIR__ . '/../Config/connection.php';
require_once __DIR__ . '/../Config/funcions.php';

// Definir constantes de configuración
define('BASE_URL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']);
define('APP_ROOT', $_SERVER['DOCUMENT_ROOT']);

define('APP_DOMAIN', 'https://elliot.cat');

define('APP_GESTIO',  "/gestio");
define('APP_AREA_USUARIS',  "/usuaris");

$base_url = '';

$isAdmin = isUserAdmin();

if ($isAdmin) {
    $base_url = BASE_URL . APP_GESTIO;
    define("APP_INTRANET", $base_url);
} else {
    $base_url = BASE_URL;
    define("APP_INTRANET", $base_url);
}

// Variables del directori de fitxers
define('APP_INTRANET_DIR',  "public/area-privada-administradors/");
define('APP_AREA_USUARIS_DIR',  "public/area-privada-usuaris/");

define('APP_HOMEPAGE_DIR',  "01_homepage/");
define('APP_COMPTABILITAT_DIR',  "02_comptabilitat/");
define('APP_CLIENTS_DIR',  "03_clients/");
define('APP_PERSONES_DIR', '04_persones/');
define('APP_PROGRAMACIO_DIR', '05_programacio/');
define('APP_PROJECTES_DIR', '06_gestor_projectes/');
define('APP_CONTACTES_DIR', '07_agenda_contactes/');
define('APP_BIBLIOTECA_DIR', '08_biblioteca_llibres/');
define('APP_ADRECES_DIR', '09_adreces_interes/');
define('APP_CLAUS_DIR', '10_claus_acces/');
define('APP_CINEMA_DIR', '11_cinema_series/');
define('APP_XARXES_DIR', '12_xarxes_socials/');
define('APP_BLOG_DIR', '13_blog/');
define('APP_RSS_DIR', '14_lector_rss/');
define('APP_HISTORIA_DIR', '15_historia/');
define('APP_AUXILIARS_DIR', '16_auxiliars/');
define('APP_VIATGES_DIR', '17_viatges/');
define('APP_USUARIS_DIR', '18_usuaris/');
define('APP_RADIO_DIR', '19_radio/');
define('APP_CV_DIR', '20_curriculum/');

// definicio de url
$url = [
    'comptabilitat' => '/comptabilitat',
    'clients' => '/clients',
    'projectes' => '/projectes',
    'biblioteca' => '/biblioteca',
    'programacio' => '/programacio',
    'adreces' => '/adreces',
    'persona' => '/base-dades-persones',
    'cinema' => '/cinema',
    'vault' => '/claus-privades',
    'persones' => '/base-dades-persones',
    'contactes' => '/agenda-contactes',
    'xarxes' => '/xarxes-socials',
    'blog' => '/blog',
    'rss' => '/lector-rss',
    'historia' => '/historia',
    'auxiliars' => '/auxiliars',
    'viatges' => '/viatges',
    'usuaris' => '/gestio-usuaris',
    'radio' => '/radio',
    'curriculum' => '/curriculum',
];
