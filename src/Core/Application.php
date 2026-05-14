<?php

declare(strict_types=1);

namespace BattleSnake\Core;

use Laminas\Diactoros\ServerRequestFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Application
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function getQueueHandler(): QueueRequestHandler
    {
        return $this->container->get(QueueRequestHandler::class);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getQueueHandler()->handle($request);
    }

    public function run(): void
    {
        $request = ServerRequestFactory::fromGlobals();
        $this->sendResponse($this->handle($request));
    }

    private function sendResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();
        $reasonPhrase = $response->getReasonPhrase();

        \header(
            \sprintf(
                'HTTP/%s %d%s',
                $response->getProtocolVersion(),
                $statusCode,
                $reasonPhrase !== '' ? ' ' . $reasonPhrase : '',
            ),
            true,
            $statusCode,
        );

        foreach ($response->getHeaders() as $name => $values) {
            $replace = true;
            foreach ($values as $value) {
                \header("$name: $value", $replace);
                $replace = false;
            }
        }

        if ($response->getBody()->isSeekable()) {
            $response->getBody()->rewind();
        }

        echo $response->getBody()->getContents();
    }
}
