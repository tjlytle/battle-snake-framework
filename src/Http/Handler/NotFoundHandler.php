<?php

declare(strict_types=1);

namespace BattleSnake\Http\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class NotFoundHandler extends AbstractHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createJsonResponse(
            [
                'error' => 'Not Found',
                'message' => 'The requested resource was not found',
            ],
            404,
        );
    }
}
