<?php

declare(strict_types=1);

namespace BattleSnake\Domain;

final readonly class Game
{
    public function __construct(
        public string $id,
        public Ruleset $ruleset,
        public string|null $map = null,
        public string|null $source = null,
        public int|null $timeout = null,
    ) {
    }
}
