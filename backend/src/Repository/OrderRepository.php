<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

class OrderRepository
{
    public function __construct(private Connection $db) {}

    public function upsert(int $shopId, string $number, float $total, string $customerName): array
    {
        $this->db->executeStatement(
            'INSERT INTO orders (shop_id, number, total, customer_name, created_at)
             VALUES (:shop_id, :number, :total, :customer_name, NOW())
             ON CONFLICT (shop_id, number) DO UPDATE SET
                total = :total,
                customer_name = :customer_name',
            [
                'shop_id' => $shopId,
                'number' => $number,
                'total' => $total,
                'customer_name' => $customerName,
            ]
        );

        return $this->db->fetchAssociative(
            'SELECT * FROM orders WHERE shop_id = :shop_id AND number = :number',
            ['shop_id' => $shopId, 'number' => $number]
        );
    }
}
