<?php

require_once __DIR__ . '../../../vendor/autoload.php';

use Dotenv\Dotenv;

$basePath = __DIR__ . '/../..';
$dotenv = Dotenv::createImmutable($basePath, '.env');
$dotenv->load();

require_once __DIR__ . '/Config/funcions.php';
require_once __DIR__ . '/Config/config.php';
require_once __DIR__ . '/Utils/verificacioSessio.php';
require_once __DIR__ . '/Utils/utils.php';
require_once __DIR__ . '/routes/routes.php';
