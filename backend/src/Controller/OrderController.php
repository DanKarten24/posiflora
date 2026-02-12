<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\OrderService;
use Doctrine\DBAL\Connection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class OrderController extends BaseController
{
    public function __construct(
        private OrderService $service,
        Connection $db,
    ) {
        parent::__construct($db);
    }

    public function create(Request $request, Response $response, string $shopId): Response
    {
        $shopId = (int) $shopId;

        if (!$this->shopExists($shopId)) {
            return $this->json($response, ['error' => 'Shop not found'], 404);
        }

        $body = $request->getParsedBody();
        $number = trim($body['number'] ?? '');
        $total = (float) ($body['total'] ?? 0);
        $customerName = trim($body['customerName'] ?? '');

        if ($number === '' || $customerName === '') {
            return $this->json($response, ['error' => 'number and customerName are required'], 400);
        }

        $result = $this->service->createOrder($shopId, $number, $total, $customerName);

        return $this->json($response, $result, 201);
    }
}
