<?php

declare(strict_types=1);

namespace BattleSnake\EventSourcing\Events;

use BattleSnake\Domain\GameState;

final readonly class End
{
    public function __construct(public GameState $state)
    {
    }
}
