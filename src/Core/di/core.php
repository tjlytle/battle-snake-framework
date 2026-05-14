<?php

declare(strict_types=1);

use BattleSnake\Core\Config;
use BattleSnake\Core\QueueRequestHandler;
use BattleSnake\Http\Handler\NotFoundHandler;
use BattleSnake\Http\Middleware\DispatchMiddleware;
use BattleSnake\Http\Middleware\JsonParser;
use Laminas\Diactoros\ResponseFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

return [
    Config::class => DI\factory(static fn(): Config => require __DIR__ . '/../../../config/config.php'),

    ResponseFactoryInterface::class => DI\autowire(ResponseFactory::class),

    NotFoundHandler::class => DI\autowire(),
    JsonParser::class => DI\autowire(),
    DispatchMiddleware::class => DI\autowire(),

    RequestHandlerInterface::class => DI\get(QueueRequestHandler::class),

    QueueRequestHandler::class => static function (ContainerInterface $c): QueueRequestHandler {
        $handler = new QueueRequestHandler($c->get(NotFoundHandler::class));
        $handler->add($c->get(JsonParser::class));
        $handler->add($c->get(DispatchMiddleware::class));
        return $handler;
    },
];
