<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

class TelegramSendLogRepository
{
    public function __construct(private Connection $db) {}

    public function exists(int $shopId, int $orderId): bool
    {
        $count = $this->db->fetchOne(
            'SELECT COUNT(*) FROM telegram_send_log WHERE shop_id = :shop_id AND order_id = :order_id',
            ['shop_id' => $shopId, 'order_id' => $orderId]
        );

        return (int) $count > 0;
    }

    public function log(int $shopId, int $orderId, string $message, string $status, ?string $error = null): void
    {
        $this->db->executeStatement(
            'INSERT INTO telegram_send_log (shop_id, order_id, message, status, error, sent_at)
             VALUES (:shop_id, :order_id, :message, :status, :error, NOW())',
            [
                'shop_id' => $shopId,
                'order_id' => $orderId,
                'message' => $message,
                'status' => $status,
                'error' => $error,
            ]
        );
    }
}
