<?php

declare(strict_types=1);

namespace BattleSnake\Strategy\Aggregate;

use BattleSnake\Analyzer\Move\Classification;
use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Game;
use BattleSnake\Strategy\Aggregate;
use BattleSnake\Strategy\Strategy;

/**
 * Calls sub-strategies in order, stopping at the first whose result
 * MoveClassifier flags SAFE. If none are safe, returns the first sub-strategy's
 * direction. Short-circuits: later strategies never run once a safe pick is found.
 */
final class FirstSafe extends Aggregate
{
    public function __construct(
        private readonly MoveClassifier $classifier,
        Strategy ...$strategies,
    ) {
        parent::__construct(...$strategies);
    }

    public function __invoke(Game $game, GameState $state): Direction
    {
        $safe = [];
        foreach (($this->classifier)($state) as $move) {
            $safe[$move->direction->value] = $move->is(Classification::SAFE);
        }

        $first = null;
        foreach ($this->strategies as $strategy) {
            $direction = ($strategy)($game, $state);
            $first ??= $direction;
            if ($safe[$direction->value] ?? false) {
                return $direction;
            }
        }

        return $first ?? Direction::UP;
    }
}
