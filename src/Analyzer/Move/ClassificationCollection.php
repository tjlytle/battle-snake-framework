<?php

declare(strict_types=1);

namespace BattleSnake\Analyzer\Move;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

readonly class ClassificationCollection implements IteratorAggregate
{
    /**
     * @var Classification[]
     */
    private array $classifications;

    /**
     * @param Classification[] $classifications
     */
    public function __construct(Classification ...$classifications)
    {
        \usort(
            $classifications, // Use the enum's name for comparison
            fn(Classification $a, Classification $b) => $a->name <=> $b->name,
        );

        $this->classifications = $classifications;
    }

    /**
     * @return Traversable<Classification>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->classifications);
    }

    /**
     * @return Classification[]
     */
    public function toArray(): array
    {
        return $this->classifications;
    }

    public function count(): int
    {
        return \count($this->classifications);
    }

    public function isEmpty(): bool
    {
        return empty($this->classifications);
    }
}
