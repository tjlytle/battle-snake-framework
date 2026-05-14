<?php

declare(strict_types=1);

namespace BattleSnake\Analyzer;

use BattleSnake\Analyzer\Move\Classification;
use BattleSnake\Analyzer\Move\ClassificationCollection;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;

/**
 * Classifies each of the four directions around our snake's head as
 * END / DANGER / HAZARD / SAFE / FOOD (composable). The geometric queries
 * live on the Coordinate / Board / Battlesnake value objects so participants
 * can call them directly from their own strategies without going through this
 * classifier.
 */
class MoveClassifier
{
    public function __invoke(GameState $state): MoveCollection
    {
        $you = $state->you;
        $head = $you->head;
        $board = $state->board;

        $moves = [];

        foreach (Direction::cases() as $direction) {
            $position = $head->adjacent($direction);
            $classifications = [];

            if ($board->hasHazardAt($position)) {
                $classifications[] = Classification::HAZARD;
            }

            if ($board->isThreatenedFor($position, $you)) {
                $classifications[] = Classification::DANGER;
            }

            if (! $board->contains($position) || $board->hasSnakeAt($position)) {
                $classifications = [Classification::END];
            }

            if ($classifications === []) {
                $classifications[] = Classification::SAFE;
            }

            // Food added last so 'safe' identifies first.
            if ($board->hasFoodAt($position)) {
                $classifications[] = Classification::FOOD;
            }

            $moves[] = new Move($direction, new ClassificationCollection(...$classifications));
        }

        return new MoveCollection(...$moves);
    }
}