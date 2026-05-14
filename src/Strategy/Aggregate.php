<?php

declare(strict_types=1);

namespace BattleSnake\Strategy;

use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Game;

/**
 * A Strategy that wraps N sub-strategies. Subclasses decide which
 * sub-strategies to invoke and how to combine their results - picking,
 * short-circuiting, or running everything are all subclass choices.
 */
abstract class Aggregate implements Strategy
{
    /** @var list<Strategy> */
    protected readonly array $strategies;

    public function __construct(Strategy ...$strategies)
    {
        $this->strategies = \array_values($strategies);
    }

    abstract public function __invoke(Game $game, GameState $state): Direction;
}
