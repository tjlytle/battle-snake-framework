<?php

declare(strict_types=1);

namespace BattleSnake\Analyzer;

use BattleSnake\Analyzer\Move\Classification;
use BattleSnake\Analyzer\Move\ClassificationCollection;
use BattleSnake\Domain\Direction;

readonly class Move
{
    public function __construct(
        public Direction $direction,
        public ClassificationCollection $classifications,
    ) {
    }

    public function is(Classification $classification): bool
    {
        return \in_array($classification, $this->classifications->toArray(), true);
    }
}
