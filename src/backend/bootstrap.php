<?php

require_once __DIR__ . '../../../vendor/autoload.php';

use Dotenv\Dotenv;

$basePath = __DIR__ . '/../..';

if (file_exists($basePath . '/.env')) {
    Dotenv::createImmutable($basePath)->load();
}

require_once __DIR__ . '/Config/funcions.php';
require_once __DIR__ . '/Config/config.php';
require_once __DIR__ . '/Utils/utils.php';
require_once __DIR__ . '/routes/routes.php';
