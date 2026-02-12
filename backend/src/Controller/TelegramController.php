<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\TelegramIntegrationService;
use Doctrine\DBAL\Connection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TelegramController extends BaseController
{
    public function __construct(
        private TelegramIntegrationService $service,
        Connection $db,
    ) {
        parent::__construct($db);
    }

    public function connect(Request $request, Response $response, string $shopId): Response
    {
        $shopId = (int) $shopId;

        if (!$this->shopExists($shopId)) {
            return $this->json($response, ['error' => 'Shop not found'], 404);
        }

        $body = $request->getParsedBody();
        $botToken = trim($body['botToken'] ?? '');
        $chatId = trim($body['chatId'] ?? '');
        $enabled = (bool) ($body['enabled'] ?? false);

        if ($botToken === '' || $chatId === '') {
            return $this->json($response, ['error' => 'botToken and chatId are required'], 400);
        }

        if (!preg_match('/^\d+:[A-Za-z0-9_-]+$/', $botToken)) {
            return $this->json($response, ['error' => 'Invalid botToken format'], 400);
        }

        if (!preg_match('/^-?\d+$/', $chatId)) {
            return $this->json($response, ['error' => 'chatId must be numeric'], 400);
        }

        $result = $this->service->connect($shopId, $botToken, $chatId, $enabled);

        return $this->json($response, $result);
    }

    public function status(Request $request, Response $response, string $shopId): Response
    {
        $shopId = (int) $shopId;

        if (!$this->shopExists($shopId)) {
            return $this->json($response, ['error' => 'Shop not found'], 404);
        }

        $status = $this->service->getStatus($shopId);

        if ($status === null) {
            return $this->json($response, ['error' => 'Integration not configured'], 404);
        }

        return $this->json($response, $status);
    }
}
