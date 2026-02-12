<?php

declare(strict_types=1);

use Slim\App;

return function (App $app): void {
    $app->addBodyParsingMiddleware();

    $app->add(function ($request, $handler) {
        $response = $handler->handle($request);
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    });

    $app->addErrorMiddleware(true, true, true);
};
