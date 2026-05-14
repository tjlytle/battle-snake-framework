<?php

declare(strict_types=1);

namespace BattleSnake\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JsonParser implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $contentType = $request->getHeaderLine('content-type');
        if (\str_contains($contentType, 'application/json')) {
            $body = (string) $request->getBody();
            $request->getBody()->rewind();

            if ($body !== '') {
                $request = $request->withParsedBody(\json_decode($body, true));
            }
        }

        return $handler->handle($request);
    }
}
