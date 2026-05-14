<?php

declare(strict_types=1);

namespace BattleSnake\Domain\Parser;

use BattleSnake\Domain\BattlesnakeCollection;
use BattleSnake\Domain\Board;

final readonly class BoardParser
{
    public function __construct(
        private BattlesnakeParser $snake_parser,
        private CoordinateFactory $coordinate_factory,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function parse(array $data): Board
    {
        $battlesnakes = \array_map(
            fn(array $snake) => $this->snake_parser->parse($snake),
            $data['snakes'] ?? [],
        );
        $snakes = new BattlesnakeCollection(...$battlesnakes);

        return new Board(
            (int)($data['height'] ?? 0),
            (int)($data['width'] ?? 0),
            $this->coordinate_factory->createCollectionFromArray($data['food'] ?? []),
            $this->coordinate_factory->createCollectionFromArray($data['hazards'] ?? []),
            $snakes,
        );
    }
}
