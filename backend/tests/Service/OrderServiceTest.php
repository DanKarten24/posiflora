<?php

declare(strict_types=1);

namespace Tests\Service;

use App\Repository\OrderRepository;
use App\Repository\TelegramIntegrationRepository;
use App\Repository\TelegramSendLogRepository;
use App\Service\OrderService;
use App\Telegram\TelegramClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    private OrderRepository&MockObject $orderRepo;
    private TelegramIntegrationRepository&MockObject $integrationRepo;
    private TelegramSendLogRepository&MockObject $logRepo;
    private TelegramClientInterface&MockObject $telegramClient;
    private OrderService $service;

    protected function setUp(): void
    {
        $this->orderRepo = $this->createMock(OrderRepository::class);
        $this->integrationRepo = $this->createMock(TelegramIntegrationRepository::class);
        $this->logRepo = $this->createMock(TelegramSendLogRepository::class);
        $this->telegramClient = $this->createMock(TelegramClientInterface::class);

        $this->service = new OrderService(
            $this->orderRepo,
            $this->integrationRepo,
            $this->logRepo,
            $this->telegramClient,
        );
    }

    public function testSentNotification(): void
    {
        $order = [
            'id' => 1,
            'shop_id' => 1,
            'number' => 'ORD-100',
            'total' => '2500.00',
            'customer_name' => 'Тест Тестов',
        ];

        $integration = [
            'shop_id' => 1,
            'bot_token' => 'fake-token',
            'chat_id' => '123456',
            'enabled' => true,
        ];

        $this->orderRepo->method('upsert')->willReturn($order);
        $this->integrationRepo->method('findByShopId')->willReturn($integration);
        $this->logRepo->method('exists')->willReturn(false);

        $this->telegramClient
            ->expects($this->once())
            ->method('sendMessage')
            ->with('fake-token', '123456', $this->anything());

        $this->logRepo
            ->expects($this->once())
            ->method('log')
            ->with(1, 1, $this->anything(), 'SENT', null);

        $result = $this->service->createOrder(1, 'ORD-100', 2500.00, 'Тест Тестов');

        $this->assertSame('sent', $result['notificationStatus']);
    }

    public function testIdempotencySkipsDuplicate(): void
    {
        $order = [
            'id' => 1,
            'shop_id' => 1,
            'number' => 'ORD-100',
            'total' => '2500.00',
            'customer_name' => 'Тест Тестов',
        ];

        $integration = [
            'shop_id' => 1,
            'bot_token' => 'fake-token',
            'chat_id' => '123456',
            'enabled' => true,
        ];

        $this->orderRepo->method('upsert')->willReturn($order);
        $this->integrationRepo->method('findByShopId')->willReturn($integration);
        $this->logRepo->method('exists')->willReturn(true);

        $this->telegramClient
            ->expects($this->never())
            ->method('sendMessage');

        $result = $this->service->createOrder(1, 'ORD-100', 2500.00, 'Тест Тестов');

        $this->assertSame('skipped', $result['notificationStatus']);
    }

    public function testFailedNotificationStillCreatesOrder(): void
    {
        $order = [
            'id' => 1,
            'shop_id' => 1,
            'number' => 'ORD-100',
            'total' => '2500.00',
            'customer_name' => 'Тест Тестов',
        ];

        $integration = [
            'shop_id' => 1,
            'bot_token' => 'fake-token',
            'chat_id' => '123456',
            'enabled' => true,
        ];

        $this->orderRepo->method('upsert')->willReturn($order);
        $this->integrationRepo->method('findByShopId')->willReturn($integration);
        $this->logRepo->method('exists')->willReturn(false);

        $this->telegramClient
            ->method('sendMessage')
            ->willThrowException(new \RuntimeException('Telegram API error'));

        $this->logRepo
            ->expects($this->once())
            ->method('log')
            ->with(1, 1, $this->anything(), 'FAILED', 'Telegram API error');

        $result = $this->service->createOrder(1, 'ORD-100', 2500.00, 'Тест Тестов');

        $this->assertSame('failed', $result['notificationStatus']);
        $this->assertNotNull($result['order']);
    }
}
