<?php

// Definir constantes de configuración
define('BASE_URL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']);
define('APP_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('APP_GESTIO',  "/gestio");

$base_url = BASE_URL . APP_GESTIO;
define("APP_INTRANET", $base_url);

// Variables del directori de fitxers
define('APP_INTRANET_DIR',  "public/intranet/");
define('APP_HOMEPAGE_DIR',  "01_homepage/");
define('APP_COMPTABILITAT_DIR',  "02_erp_comptabilitat/");
define('APP_CLIENTS_DIR',  "03_crm_clients/");
define('APP_PERSONES_DIR', '04_persones/');
define('APP_PROJECTES_DIR', '06_gestor_projectes/');
define('APP_CONTACTES_DIR', '07_agenda_contactes/');
define('APP_BIBLIOTECA_DIR', '08_biblioteca_llibres/');
define('APP_ADRECES_DIR', '09_adreces_interes/');
define('APP_CLAUS_DIR', '10_claus_acces/');
define('APP_CINEMA_DIR', '11_cinema_series/');

// Cargar librerías externas
require_once __DIR__ . '/../../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();
require_once __DIR__ . '/../Config/connection.php';

// definicio de url
$url = [
    'comptabilitat' => '/comptabilitat',
    'clients' => '/clients',
    'projectes' => '/projectes',
    'biblioteca' => '/biblioteca',
    'adreces' => '/adreces',
    'persona' => '/persona',
    'cinema' => '/cinema',
    'auxiliars' => '/auxiliars',
    'vault' => '/claus-privades',
    'persones' => '/persona',
    'contactes' => '/agenda-contactes',

];
