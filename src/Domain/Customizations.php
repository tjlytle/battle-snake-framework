<?php

declare(strict_types=1);

namespace BattleSnake\Domain;

final readonly class Customizations
{
    public function __construct(
        public string|null $color = null,
        public string|null $head = null,
        public string|null $tail = null,
    ) {
    }
}
