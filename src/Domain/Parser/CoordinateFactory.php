<?php

declare(strict_types=1);

namespace BattleSnake\Domain\Parser;

use BattleSnake\Domain\Coordinate;
use BattleSnake\Domain\CoordinateCollection;

final class CoordinateFactory
{
    /**
     * Create a Coordinate object from an array containing x/y coordinates
     *
     * @param array<string, mixed> $data
     */
    public function createFromArray(array $data): Coordinate
    {
        return new Coordinate(
            (int)($data['x'] ?? 0),
            (int)($data['y'] ?? 0),
        );
    }

    /**
     * Create a CoordinateCollection from an array of arrays containing x/y coordinates
     *
     * @param array<array<string, mixed>> $dataArray
     */
    public function createCollectionFromArray(array $dataArray): CoordinateCollection
    {
        $coordinates = \array_map(
            fn(array $item) => $this->createFromArray($item),
            $dataArray,
        );

        return new CoordinateCollection(...$coordinates);
    }
}
