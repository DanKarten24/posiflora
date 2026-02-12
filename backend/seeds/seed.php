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

$shops = [
    [1, 'Demo 1'],
    [2, 'Demo 2'],
];

$shopStmt = $pdo->prepare(
    'INSERT INTO shops (id, name) VALUES (:id, :name) ON CONFLICT (id) DO NOTHING'
);

foreach ($shops as [$id, $name]) {
    $shopStmt->execute(['id' => $id, 'name' => $name]);
}

$orders = [
    [1, 'ORD-001', 1500.00, 'Анна'],
    [1, 'ORD-002', 2300.50, 'Пётр Сидоров'],
    [1, 'ORD-003', 890.00, 'Мария Козлова'],
    [1, 'ORD-004', 4200.00, 'Дмитрий Волков'],
    [1, 'ORD-005', 1750.00, 'Елена'],
    [1, 'ORD-006', 3100.00, 'Алексей Морозов'],
    [1, 'ORD-007', 950.00, 'Ольга Белова'],

    [2, 'ORD-101', 3200.00, 'Игорь Петров'],
    [2, 'ORD-102', 1800.00, 'Наталья Соколова'],
    [2, 'ORD-103', 4500.00, 'Сергей Кузнецов'],
    [2, 'ORD-104', 720.00, 'Татьяна'],
    [2, 'ORD-105', 2900.00, 'Андрей Попов'],
    [2, 'ORD-106', 1350.00, 'Ирина Васильева'],
    [2, 'ORD-107', 5100.00, 'Виктор Николаев'],
];

$stmt = $pdo->prepare(
    'INSERT INTO orders (shop_id, number, total, customer_name) VALUES (:shop_id, :number, :total, :customer_name)'
);

foreach ($orders as [$shopId, $number, $total, $name]) {
    $stmt->execute(['shop_id' => $shopId, 'number' => $number, 'total' => $total, 'customer_name' => $name]);
}

echo "Seeded " . count($shops) . " shop(s) and " . count($orders) . " orders.\n";
