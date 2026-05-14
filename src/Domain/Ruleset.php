<?php

declare(strict_types=1);

namespace BattleSnake\Domain;

use BattleSnake\Domain\Setting\RulesetSettings;

final readonly class Ruleset
{
    public function __construct(
        public string $name,
        public string $version,
        public RulesetSettings $settings,
    ) {
    }
}
