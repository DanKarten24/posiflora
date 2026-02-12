<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\OrderRepository;
use App\Repository\TelegramIntegrationRepository;
use App\Repository\TelegramSendLogRepository;
use App\Telegram\TelegramClientInterface;

class OrderService
{
    private const PRICE_DECIMALS = 2;

    public function __construct(
        private OrderRepository $orderRepo,
        private TelegramIntegrationRepository $integrationRepo,
        private TelegramSendLogRepository $logRepo,
        private TelegramClientInterface $telegramClient,
    ) {}

    public function createOrder(int $shopId, string $number, float $total, string $customerName): array
    {
        $order = $this->orderRepo->upsert($shopId, $number, $total, $customerName);

        $notificationStatus = $this->sendNotification($shopId, (int) $order['id'], $order);

        return [
            'order' => $order,
            'notificationStatus' => $notificationStatus,
        ];
    }

    private function sendNotification(int $shopId, int $orderId, array $order): string
    {
        $integration = $this->integrationRepo->findByShopId($shopId);

        if (!$integration || !$integration['enabled']) {
            return 'disabled';
        }

        if ($this->logRepo->exists($shopId, $orderId)) {
            return 'skipped';
        }

        $message = $this->formatMessage($order);

        try {
            $this->telegramClient->sendMessage(
                $integration['bot_token'],
                $integration['chat_id'],
                $message
            );
            $this->logRepo->log($shopId, $orderId, $message, 'SENT');
            return 'sent';
        } catch (\Throwable $e) {
            $this->logRepo->log($shopId, $orderId, $message, 'FAILED', $e->getMessage());
            return 'failed';
        }
    }

    private function formatMessage(array $order): string
    {
        return sprintf(
            'Новый заказ %s на сумму %s ₽, клиент %s',
            $order['number'],
            number_format((float) $order['total'], self::PRICE_DECIMALS, '.', ' '),
            $order['customer_name']
        );
    }
}
