<?php

declare(strict_types=1);

namespace BattleSnake\Domain\Parser;

use BattleSnake\Domain\GameState;

final class GameStateParser
{
    public function __construct(
        private GameParser $gameParser,
        private BoardParser $boardParser,
        private BattlesnakeParser $snakeParser,
    ) {
    }

    /**
     * Parse JSON data into a GameState object
     *
     * @param array<string, mixed> $data
     */
    public function parse(array $data): GameState
    {
        return new GameState(
            $this->gameParser->parse($data['game'] ?? []),
            (int)($data['turn'] ?? 0),
            $this->boardParser->parse($data['board'] ?? []),
            $this->snakeParser->parse($data['you'] ?? []),
        );
    }
}
