<?php

declare(strict_types=1);

namespace App\Telegram;

use GuzzleHttp\Client;

class HttpTelegramClient implements TelegramClientInterface
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://api.telegram.org']);
    }

    public function sendMessage(string $botToken, string $chatId, string $text): void
    {
        $this->client->post("/bot{$botToken}/sendMessage", [
            'json' => [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ],
        ]);
    }
}
