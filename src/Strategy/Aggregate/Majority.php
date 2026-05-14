<?php

declare(strict_types=1);

namespace BattleSnake\Strategy\Aggregate;

use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Game;
use BattleSnake\Strategy\Aggregate;

/**
 * Invokes every sub-strategy and returns the direction that received the most votes.
 * Ties are broken by enum declaration order (UP, DOWN, LEFT, RIGHT).
 */
final class Majority extends Aggregate
{
    public function __invoke(Game $game, GameState $state): Direction
    {
        $votes = [];
        foreach ($this->strategies as $strategy) {
            $direction = ($strategy)($game, $state);
            $votes[$direction->value] = ($votes[$direction->value] ?? 0) + 1;
        }

        if ($votes === []) {
            return Direction::UP;
        }

        \arsort($votes);
        return Direction::from((string) \array_key_first($votes));
    }
}
