<?php

declare(strict_types=1);

namespace BattleSnake\Domain\Setting;

final readonly class RoyaleSettings
{
    public function __construct(
        public int $shrinkEveryNTurns,
    ) {
    }
}
