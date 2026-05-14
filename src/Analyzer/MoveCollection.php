<?php

declare(strict_types=1);

namespace BattleSnake\Analyzer;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

readonly class MoveCollection implements IteratorAggregate
{
    /**
     * @var Move[]
     */
    private array $moves;

    /**
     * @param Move[] $moves
     */
    public function __construct(Move ...$moves)
    {
        \usort(
            $moves, // Use the enum's name for comparison
            fn(Move $a, Move $b) => $a->direction->name <=> $b->direction->name,
        );

        $this->moves = $moves;
    }

    /**
     * @return Traversable<Move>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->moves);
    }

    /**
     * @return Move[]
     */
    public function toArray(): array
    {
        return $this->moves;
    }

    public function count(): int
    {
        return \count($this->moves);
    }

    public function isEmpty(): bool
    {
        return empty($this->moves);
    }
}
