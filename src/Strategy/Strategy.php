<?php

declare(strict_types=1);

namespace BattleSnake\Strategy;

use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Game;

interface Strategy
{
    public function __invoke(Game $game, GameState $state): Direction;
}
