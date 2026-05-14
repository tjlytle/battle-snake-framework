<?php

declare(strict_types=1);

namespace BattleSnake\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DispatchMiddleware implements MiddlewareInterface
{
    /** @var array<string, RequestHandlerInterface> */
    private array $routes = [];

    public function addRoute(string $path, RequestHandlerInterface $handler): void
    {
        $this->routes[$path] = $handler;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        if (isset($this->routes[$path])) {
            return $this->routes[$path]->handle($request);
        }

        return $handler->handle($request);
    }
}
