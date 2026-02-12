<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Psr\Http\Message\ResponseInterface as Response;

abstract class BaseController
{
    public function __construct(protected Connection $db) {}

    protected function shopExists(int $shopId): bool
    {
        return (bool) $this->db->fetchOne('SELECT 1 FROM shops WHERE id = :id', ['id' => $shopId]);
    }

    protected function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
