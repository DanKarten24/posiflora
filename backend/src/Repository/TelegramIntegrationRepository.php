<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

class TelegramIntegrationRepository
{
    private const STATS_INTERVAL_DAYS = 7;
    private const CHAT_ID_VISIBLE_CHARS = 4;

    public function __construct(private Connection $db) {}

    public function upsert(int $shopId, string $botToken, string $chatId, bool $enabled): array
    {
        $this->db->executeStatement(
            'INSERT INTO telegram_integrations (shop_id, bot_token, chat_id, enabled, created_at, updated_at)
             VALUES (:shop_id, :bot_token, :chat_id, :enabled, NOW(), NOW())
             ON CONFLICT (shop_id) DO UPDATE SET
                bot_token = :bot_token,
                chat_id = :chat_id,
                enabled = :enabled,
                updated_at = NOW()',
            [
                'shop_id' => $shopId,
                'bot_token' => $botToken,
                'chat_id' => $chatId,
                'enabled' => $enabled ? 'true' : 'false',
            ]
        );

        return $this->findByShopId($shopId);
    }

    public function findByShopId(int $shopId): ?array
    {
        $row = $this->db->fetchAssociative(
            'SELECT * FROM telegram_integrations WHERE shop_id = :shop_id',
            ['shop_id' => $shopId]
        );

        return $row ?: null;
    }

    public function getStatus(int $shopId): ?array
    {
        $integration = $this->findByShopId($shopId);
        if (!$integration) {
            return null;
        }

        $stats = $this->db->fetchAssociative(
            "SELECT
                COUNT(*) FILTER (WHERE status = 'SENT') AS sent_count,
                COUNT(*) FILTER (WHERE status = 'FAILED') AS failed_count,
                MAX(sent_at) FILTER (WHERE status = 'SENT') AS last_sent_at
             FROM telegram_send_log
             WHERE shop_id = :shop_id AND sent_at >= NOW() - INTERVAL '" . self::STATS_INTERVAL_DAYS . " days'",
            ['shop_id' => $shopId]
        );

        $chatId = $integration['chat_id'];
        $visible = self::CHAT_ID_VISIBLE_CHARS;
        $masked = strlen($chatId) > $visible
            ? str_repeat('*', strlen($chatId) - $visible) . substr($chatId, -$visible)
            : $chatId;

        return [
            'enabled' => $integration['enabled'],
            'chatId' => $masked,
            'lastSentAt' => $stats['last_sent_at'],
            'sentCount' => (int) $stats['sent_count'],
            'failedCount' => (int) $stats['failed_count'],
        ];
    }
}
