<?php

declare(strict_types=1);

namespace App\Telegram;

interface TelegramClientInterface
{
    public function sendMessage(string $botToken, string $chatId, string $text): void;
}
