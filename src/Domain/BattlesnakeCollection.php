<?php

declare(strict_types=1);

namespace BattleSnake\Domain;

use ArrayIterator;
use Crell\Serde\Attributes\SequenceField;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, Battlesnake>
 */
readonly class BattlesnakeCollection implements IteratorAggregate
{
    /**
     * @var array<Battlesnake>
     */
    #[SequenceField(arrayType: Battlesnake::class)]
    public array $battlesnakes;

    public function __construct(Battlesnake ...$battlesnakes)
    {
        $this->battlesnakes = $battlesnakes;
    }

    /**
     * @return Traversable<int, Battlesnake>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->battlesnakes);
    }
}
