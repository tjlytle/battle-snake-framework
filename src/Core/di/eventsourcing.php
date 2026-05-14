<?php

declare(strict_types=1);

use BattleSnake\EventSourcing\Game;
use BattleSnake\EventSourcing\Projection\GameStatProjector;
use BattleSnake\EventSourcing\SerdePayloadSerializer;
use Crell\Serde\Serde;
use Crell\Serde\SerdeCommon;
use Doctrine\DBAL\Connection;
use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\EventSourcing\Serialization\PayloadSerializer;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use EventSauce\IdEncoding\StringIdEncoder;
use EventSauce\MessageRepository\DoctrineMessageRepository\DoctrineMessageRepository;
use Psr\Container\ContainerInterface;

return [
    Serde::class => DI\autowire(SerdeCommon::class),
    PayloadSerializer::class => DI\autowire(SerdePayloadSerializer::class),

    // ConstructingMessageSerializer's PayloadSerializer arg is nullable with a
    // default; PHP-DI's autowiring skips nullable optional params, so we have to
    // wire the dependency explicitly or it falls back to the EventSauce default.
    MessageSerializer::class => DI\autowire(ConstructingMessageSerializer::class)
        ->constructorParameter('payloadSerializer', DI\get(PayloadSerializer::class)),

    MessageRepository::class => static function (ContainerInterface $c): MessageRepository {
        // String-encoded UUIDs play nicely with SQLite and MySQL alike;
        // binary encoders save space but require BLOB columns.
        return new DoctrineMessageRepository(
            connection: $c->get(Connection::class),
            tableName: 'domain_messages',
            serializer: $c->get(MessageSerializer::class),
            aggregateRootIdEncoder: new StringIdEncoder(),
            eventIdEncoder: new StringIdEncoder(),
        );
    },

    MessageDispatcher::class => static function (ContainerInterface $c): MessageDispatcher {
        return new SynchronousMessageDispatcher(
            $c->get(GameStatProjector::class),
        );
    },

    AggregateRootRepository::class => static function (ContainerInterface $c): AggregateRootRepository {
        return new EventSourcedAggregateRootRepository(
            aggregateRootClassName: Game::class,
            messageRepository: $c->get(MessageRepository::class),
            dispatcher: $c->get(MessageDispatcher::class),
        );
    },

    GameStatProjector::class => DI\autowire(),
];
