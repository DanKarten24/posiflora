<?php

declare(strict_types=1);

use DI\Bridge\Slim\Bridge;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

$containerDefinitions = require __DIR__ . '/../config/container.php';
$container = new \DI\Container($containerDefinitions);

$app = Bridge::create($container);

$middleware = require __DIR__ . '/../config/middleware.php';
$middleware($app);

$routes = require __DIR__ . '/../config/routes.php';
$routes($app);

$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

$app->run();
