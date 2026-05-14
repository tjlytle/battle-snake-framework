<?php

declare(strict_types=1);

namespace BattleSnake\Domain\Parser;

use BattleSnake\Domain\Game;
use BattleSnake\Domain\Ruleset;

final class GameParser
{
    public function __construct(
        private RulesetSettingsParser $rulesetSettingsParser,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function parse(array $data): Game
    {
        $ruleset = $data['ruleset'] ?? [];

        return new Game(
            id: $data['id'] ?? '',
            ruleset: new Ruleset(
                $ruleset['name'] ?? '',
                $ruleset['version'] ?? '',
                $this->rulesetSettingsParser->parse($ruleset['settings'] ?? []),
            ),
            map: $data['map'] ?? '',
            source: $data['source'] ?? '',
            timeout: isset($data['timeout']) ? (int)$data['timeout'] : null,
        );
    }
}
