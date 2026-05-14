<?php

declare(strict_types=1);

namespace BattleSnake\Domain\Parser;

use BattleSnake\Domain\Setting\RoyaleSettings;
use BattleSnake\Domain\Setting\RulesetSettings;
use BattleSnake\Domain\Setting\SquadSettings;

final class RulesetSettingsParser
{
    /**
     * @param array<string, mixed> $data
     */
    public function parse(array $data): RulesetSettings
    {
        $royaleSettings = null;
        if (isset($data['royale']) && \is_array($data['royale'])) {
            $royaleSettings = new RoyaleSettings(
                (int)($data['royale']['shrinkEveryNTurns'] ?? 0),
            );
        }

        $squadSettings = null;
        if (isset($data['squad']) && \is_array($data['squad'])) {
            $squadSettings = new SquadSettings(
                (bool)($data['squad']['allowBodyCollisions'] ?? false),
                (bool)($data['squad']['sharedElimination'] ?? false),
                (bool)($data['squad']['sharedHealth'] ?? false),
                (bool)($data['squad']['sharedLength'] ?? false),
            );
        }

        return new RulesetSettings(
            (int)($data['foodSpawnChance'] ?? 0),
            (int)($data['minimumFood'] ?? 0),
            (int)($data['hazardDamagePerTurn'] ?? 0),
            $royaleSettings,
            $squadSettings,
        );
    }
}
