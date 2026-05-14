<?php

declare(strict_types=1);

namespace BattleSnake\EventSourcing\Events;

use BattleSnake\Domain\Direction;

final readonly class Nudge
{
    public function __construct(public Direction $direction)
    {
    }
}
