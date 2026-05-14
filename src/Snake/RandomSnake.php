<?php

declare(strict_types=1);

namespace BattleSnake\Snake;

use BattleSnake\Analyzer\Move\Classification;
use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Game;
use BattleSnake\Strategy\Strategy;

/**
 * Sample snake bundled with the platform. Participants are expected to
 * either replace this entirely or override the Strategy DI binding to
 * point at their own class. Logic:
 *
 *   - prefer SAFE + FOOD directions
 *   - else any SAFE direction
 *   - else any non-END direction
 *   - else UP (and die honourably)
 *
 * Reads from $game->last_move to avoid an immediate U-turn when otherwise tied.
 */
final class RandomSnake implements Strategy
{
    public function __construct(private readonly MoveClassifier $classifier)
    {
    }

    public function __invoke(Game $game, GameState $state): Direction
    {
        $best = [];
        $safe = [];
        $any = [];

        foreach (($this->classifier)($state) as $move) {
            if ($move->is(Classification::END)) {
                continue;
            }
            $any[] = $move->direction;

            if ($move->is(Classification::SAFE)) {
                $safe[] = $move->direction;
                if ($move->is(Classification::FOOD)) {
                    $best[] = $move->direction;
                }
            }
        }

        $pool = $best !== [] ? $best : ($safe !== [] ? $safe : $any);

        if ($pool === []) {
            return Direction::UP;
        }

        $pool = $this->discourageReversal($pool, $game->last_move);
        return $pool[\array_rand($pool)];
    }

    /**
     * @param list<Direction> $pool
     * @return list<Direction>
     */
    private function discourageReversal(array $pool, ?Direction $last): array
    {
        if ($last === null || \count($pool) === 1) {
            return $pool;
        }

        $reverse = match ($last) {
            Direction::UP => Direction::DOWN,
            Direction::DOWN => Direction::UP,
            Direction::LEFT => Direction::RIGHT,
            Direction::RIGHT => Direction::LEFT,
        };

        $filtered = \array_values(\array_filter($pool, static fn(Direction $d) => $d !== $reverse));
        return $filtered !== [] ? $filtered : $pool;
    }
}
