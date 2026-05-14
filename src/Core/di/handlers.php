<?php

declare(strict_types=1);

use BattleSnake\Http\Handler\EndHandler;
use BattleSnake\Http\Handler\InfoHandler;
use BattleSnake\Http\Handler\ManualHandler;
use BattleSnake\Http\Handler\MoveHandler;
use BattleSnake\Http\Handler\StartHandler;
use BattleSnake\Http\Middleware\DispatchMiddleware;
use Psr\Container\ContainerInterface;

return [
    InfoHandler::class => DI\autowire(),
    StartHandler::class => DI\autowire(),
    MoveHandler::class => DI\autowire(),
    EndHandler::class => DI\autowire(),
    ManualHandler::class => DI\autowire(),

    // The DispatchMiddleware is created by core.php; here we decorate it with routes.
    DispatchMiddleware::class => static function (ContainerInterface $c): DispatchMiddleware {
        $dispatch = new DispatchMiddleware();
        $dispatch->addRoute('/', $c->get(InfoHandler::class));
        $dispatch->addRoute('/start', $c->get(StartHandler::class));
        $dispatch->addRoute('/move', $c->get(MoveHandler::class));
        $dispatch->addRoute('/end', $c->get(EndHandler::class));
        $dispatch->addRoute('/manual', $c->get(ManualHandler::class));
        return $dispatch;
    },
];
