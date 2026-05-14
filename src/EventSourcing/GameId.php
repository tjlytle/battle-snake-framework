<?php

declare(strict_types=1);

namespace BattleSnake\EventSourcing;

use EventSauce\EventSourcing\AggregateRootId;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class GameId implements AggregateRootId
{
    private function __construct(private UuidInterface $uuid)
    {
    }

    public static function fromString(string $aggregateRootId): static
    {
        return new self(Uuid::fromString($aggregateRootId));
    }

    public static function fromUuid(UuidInterface $uuid): self
    {
        return new self($uuid);
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4());
    }

    public function toString(): string
    {
        return $this->uuid->toString();
    }
}
