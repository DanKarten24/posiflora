<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

$settings = require __DIR__ . '/../config/settings.php';
$db = $settings['db'];

$dsn = "pgsql:host={$db['host']};port={$db['port']};dbname={$db['dbname']}";
$pdo = new PDO($dsn, $db['user'], $db['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS shops (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS telegram_integrations (
    id SERIAL PRIMARY KEY,
    shop_id INT NOT NULL UNIQUE REFERENCES shops(id),
    bot_token TEXT NOT NULL,
    chat_id TEXT NOT NULL,
    enabled BOOLEAN NOT NULL DEFAULT false,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS orders (
    id SERIAL PRIMARY KEY,
    shop_id INT NOT NULL REFERENCES shops(id),
    number TEXT NOT NULL,
    total NUMERIC(10,2) NOT NULL DEFAULT 0,
    customer_name TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(shop_id, number)
);

CREATE TABLE IF NOT EXISTS telegram_send_log (
    id SERIAL PRIMARY KEY,
    shop_id INT NOT NULL,
    order_id INT NOT NULL,
    message TEXT,
    status VARCHAR(10) NOT NULL CHECK (status IN ('SENT', 'FAILED')),
    error TEXT,
    sent_at TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(shop_id, order_id)
);
SQL;

$pdo->exec($sql);

echo "Migration completed successfully.\n";
