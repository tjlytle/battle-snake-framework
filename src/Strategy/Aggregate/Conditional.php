<?php

declare(strict_types=1);

namespace BattleSnake\Strategy\Aggregate;

use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Game;
use BattleSnake\Strategy\Aggregate;
use BattleSnake\Strategy\Strategy;
use Closure;
use OutOfBoundsException;

/**
 * Given a picker (Game, GameState): int, picks ONE sub-strategy by the
 * returned index and invokes only it. No wasted strategy invocations.
 */
final class Conditional extends Aggregate
{
    /**
     * @param Closure(Game, GameState): int $picker
     */
    public function __construct(
        private readonly Closure $picker,
        Strategy ...$strategies,
    ) {
        parent::__construct(...$strategies);
    }

    public function __invoke(Game $game, GameState $state): Direction
    {
        $index = ($this->picker)($game, $state);
        $strategy = $this->strategies[$index] ?? null;

        if ($strategy === null) {
            throw new OutOfBoundsException(\sprintf(
                'Conditional picker returned index %d but only %d strategies are configured',
                $index,
                \count($this->strategies),
            ));
        }

        return ($strategy)($game, $state);
    }
}
