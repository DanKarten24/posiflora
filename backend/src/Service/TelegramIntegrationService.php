<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\TelegramIntegrationRepository;

class TelegramIntegrationService
{
    public function __construct(private TelegramIntegrationRepository $repo) {}

    public function connect(int $shopId, string $botToken, string $chatId, bool $enabled): array
    {
        return $this->repo->upsert($shopId, $botToken, $chatId, $enabled);
    }

    public function getStatus(int $shopId): ?array
    {
        return $this->repo->getStatus($shopId);
    }
}
