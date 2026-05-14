<?php

declare(strict_types=1);

namespace BattleSnake\Domain;

final readonly class GameState
{
    public function __construct(
        public Game $game,
        public int $turn,
        public Board $board,
        public Battlesnake $you,
    ) {
    }
}
