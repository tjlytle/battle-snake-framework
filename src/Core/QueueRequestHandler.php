<?php

declare(strict_types=1);

namespace BattleSnake\Core;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PSR-15 middleware queue. Each call to handle() walks the middleware list
 * via an immutable index so the handler can be reused across multiple
 * requests (important for tests and any long-lived runtime).
 */
class QueueRequestHandler implements RequestHandlerInterface
{
    /** @var list<MiddlewareInterface> */
    private array $middleware = [];

    public function __construct(private readonly RequestHandlerInterface $fallback_handler)
    {
    }

    public function add(MiddlewareInterface $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->processAt(0, $request);
    }

    private function processAt(int $index, ServerRequestInterface $request): ResponseInterface
    {
        if (! isset($this->middleware[$index])) {
            return $this->fallback_handler->handle($request);
        }

        return $this->middleware[$index]->process($request, $this->continuation($index + 1));
    }

    private function continuation(int $next): RequestHandlerInterface
    {
        return new readonly class ($this, $next) implements RequestHandlerInterface {
            public function __construct(
                private QueueRequestHandler $outer,
                private int $next,
            ) {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->outer->process($this->next, $request);
            }
        };
    }

    /** @internal */
    public function process(int $index, ServerRequestInterface $request): ResponseInterface
    {
        return $this->processAt($index, $request);
    }
}
