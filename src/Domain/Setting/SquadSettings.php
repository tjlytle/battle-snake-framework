<?php

declare(strict_types=1);

namespace BattleSnake\Domain\Setting;

final readonly class SquadSettings
{
    public function __construct(
        public bool $allowBodyCollisions,
        public bool $sharedElimination,
        public bool $sharedHealth,
        public bool $sharedLength,
    ) {
    }
}
