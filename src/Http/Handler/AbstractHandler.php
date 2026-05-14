<?php

declare(strict_types=1);

namespace BattleSnake\Http\Handler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class AbstractHandler implements RequestHandlerInterface
{
    public function __construct(protected readonly ResponseFactoryInterface $response_factory)
    {
    }

    protected function createJsonResponse(array $data, int $status = 200): ResponseInterface
    {
        $response = $this->response_factory->createResponse($status)
            ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write((string) \json_encode($data));
        return $response;
    }
}
