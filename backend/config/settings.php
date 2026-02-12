<?php

declare(strict_types=1);

return [
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? 'db',
        'port' => $_ENV['DB_PORT'] ?? '5432',
        'dbname' => $_ENV['DB_NAME'] ?? 'posiflora',
        'user' => $_ENV['DB_USER'] ?? 'posiflora',
        'password' => $_ENV['DB_PASSWORD'] ?? 'secret',
        'driver' => 'pdo_pgsql',
    ],
];
