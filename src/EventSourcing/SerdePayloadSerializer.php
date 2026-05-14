<?php

declare(strict_types=1);

namespace BattleSnake\EventSourcing;

use Crell\Serde\Serde;
use EventSauce\EventSourcing\Serialization\PayloadSerializer;

/**
 * EventSauce PayloadSerializer adapter that delegates to Crell\Serde so every
 * event type (whether it carries a primitive Direction or a full GameState
 * tree) flows through one serialization path.
 */
final readonly class SerdePayloadSerializer implements PayloadSerializer
{
    public function __construct(private Serde $serde)
    {
    }

    public function serializePayload(object $event): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->serde->serialize($event, 'array');
        return $result;
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return T
     */
    public function unserializePayload(string $className, array $payload): object
    {
        return $this->serde->deserialize($payload, from: 'array', to: $className);
    }
}
