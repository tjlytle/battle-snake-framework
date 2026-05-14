<?php

declare(strict_types=1);

namespace BattleSnake\Domain;

enum Direction: string
{
    case UP = 'up';
    case DOWN = 'down';
    case LEFT = 'left';
    case RIGHT = 'right';
}
