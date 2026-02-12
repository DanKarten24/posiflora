<?php

declare(strict_types=1);

use App\Controller\OrderController;
use App\Controller\TelegramController;
use Slim\App;

return function (App $app): void {
    $app->post('/shops/{shopId}/telegram/connect', [TelegramController::class, 'connect']);
    $app->get('/shops/{shopId}/telegram/status', [TelegramController::class, 'status']);
    $app->post('/shops/{shopId}/orders', [OrderController::class, 'create']);
};
