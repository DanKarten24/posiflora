<?php

declare(strict_types=1);

use App\Telegram\HttpTelegramClient;
use App\Telegram\TelegramClientInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Psr\Container\ContainerInterface;

return [
    Connection::class => function (ContainerInterface $c): Connection {
        $settings = $c->get('settings')['db'];
        return DriverManager::getConnection($settings);
    },

    TelegramClientInterface::class => \DI\autowire(HttpTelegramClient::class),

    'settings' => function (): array {
        return require __DIR__ . '/settings.php';
    },
];
