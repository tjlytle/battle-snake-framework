<?php

declare(strict_types=1);

namespace BattleSnake\Analyzer\Move;

enum Classification
{
    case END;
    case DANGER;
    case HAZARD;
    case FOOD;
    case SAFE;
}
