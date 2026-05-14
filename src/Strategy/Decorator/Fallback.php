<?php

declare(strict_types=1);

namespace BattleSnake\Strategy\Decorator;

use BattleSnake\Analyzer\Move\Classification;
use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Game;
use BattleSnake\Strategy\Strategy;

/**
 * Runs `primary`. If the primary's direction is classified END, delegate to `fallback`.
 * Useful for wrapping a clever-but-occasionally-suicidal strategy with a safety net.
 */
final class Fallback implements Strategy
{
    public function __construct(
        private readonly Strategy $primary,
        private readonly Strategy $fallback,
        private readonly MoveClassifier $classifier,
    ) {
    }

    public function __invoke(Game $game, GameState $state): Direction
    {
        $direction = ($this->primary)($game, $state);

        foreach (($this->classifier)($state) as $move) {
            if ($move->direction !== $direction) {
                continue;
            }
            if ($move->is(Classification::END)) {
                return ($this->fallback)($game, $state);
            }
            return $direction;
        }

        return ($this->fallback)($game, $state);
    }
}
