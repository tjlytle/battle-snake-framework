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
 * If the Game has a pending nudge (set via the /manual endpoint) and that
 * direction is currently SAFE per MoveClassifier, return it. Otherwise
 * delegate to the wrapped fallback Strategy.
 */
final class Nudgeable implements Strategy
{
    public function __construct(
        private readonly Strategy $fallback,
        private readonly MoveClassifier $classifier,
    ) {
    }

    public function __invoke(Game $game, GameState $state): Direction
    {
        if ($game->last_nudge === null) {
            return ($this->fallback)($game, $state);
        }

        foreach (($this->classifier)($state) as $move) {
            if ($move->direction !== $game->last_nudge) {
                continue;
            }

            if ($move->is(Classification::SAFE)) {
                return $move->direction;
            }
            break;
        }

        return ($this->fallback)($game, $state);
    }
}
