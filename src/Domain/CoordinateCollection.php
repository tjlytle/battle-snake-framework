<?php

declare(strict_types=1);

namespace BattleSnake\Domain;

use ArrayIterator;
use Crell\Serde\Attributes\SequenceField;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, Coordinate>
 */
readonly class CoordinateCollection implements IteratorAggregate
{
    /**
     * @var array<Coordinate>
     */
    #[SequenceField(arrayType: Coordinate::class)]
    public array $coordinates;

    public function __construct(Coordinate ...$coordinates)
    {
        $this->coordinates = $coordinates;
    }

    /**
     * @return Traversable<int, Coordinate>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->coordinates);
    }
}
