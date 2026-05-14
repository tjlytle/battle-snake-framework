<?php

declare(strict_types=1);

namespace BattleSnake\Strategy\Aggregate;

use BattleSnake\Analyzer\Move;
use BattleSnake\Analyzer\Move\Classification;
use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Game;
use BattleSnake\Strategy\Aggregate;
use BattleSnake\Strategy\Strategy;

/**
 * Invokes every sub-strategy, scores each result via MoveClassifier
 * (FOOD+SAFE > SAFE > DANGER > HAZARD > END), returns the highest-scoring direction.
 * Ties resolved by sub-strategy declaration order.
 */
final class Scored extends Aggregate
{
    public function __construct(
        private readonly MoveClassifier $classifier,
        Strategy ...$strategies,
    ) {
        parent::__construct(...$strategies);
    }

    public function __invoke(Game $game, GameState $state): Direction
    {
        $score_for = [];
        foreach (($this->classifier)($state) as $move) {
            $score_for[$move->direction->value] = self::score($move);
        }

        $best = null;
        $best_score = \PHP_INT_MIN;
        foreach ($this->strategies as $strategy) {
            $direction = ($strategy)($game, $state);
            $score = $score_for[$direction->value] ?? \PHP_INT_MIN;
            if ($score > $best_score) {
                $best_score = $score;
                $best = $direction;
            }
        }

        return $best ?? Direction::UP;
    }

    private static function score(Move $move): int
    {
        if ($move->is(Classification::END)) {
            return -10;
        }

        $score = match (true) {
            $move->is(Classification::SAFE) => 3,
            $move->is(Classification::DANGER) => 1,
            $move->is(Classification::HAZARD) => 0,
            default => 0,
        };

        if ($move->is(Classification::FOOD)) {
            $score += 1;
        }

        return $score;
    }
}
